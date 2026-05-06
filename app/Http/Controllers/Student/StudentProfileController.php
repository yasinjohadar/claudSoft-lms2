<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\UpdateProfileRequest;
use App\Http\Requests\Student\ChangePasswordRequest;
use App\Models\Nationality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Storage\StorageHelperService;

class StudentProfileController extends Controller
{
    protected StorageHelperService $storageHelper;

    public function __construct(StorageHelperService $storageHelper)
    {
        $this->storageHelper = $storageHelper;
    }
    /**
     * Display the student's profile.
     */
    public function index()
    {
        $student = auth()->user()->load('nationality');

        return view('student.pages.profile.index', compact('student'));
    }

    /**
     * Show the form for editing the student's profile.
     */
    public function edit()
    {
        $student = auth()->user();
        $nationalities = Nationality::all();

        return view('student.pages.profile.edit', compact('student', 'nationalities'));
    }

    /**
     * Update the student's profile information.
     */
    public function update(UpdateProfileRequest $request)
    {
        try {
            DB::beginTransaction();

            $student = auth()->user();

            // Update basic information
            $student->name = $request->name;
            // Email cannot be changed by student

            // Update all fields (including nullable ones)
            $student->name_ar = $request->input('name_ar');
            $student->country_code = $request->input('country_code');
            $student->phone = $request->input('phone');
            $student->national_id = $request->input('national_id');
            $student->date_of_birth = $request->input('date_of_birth');
            $student->gender = $request->input('gender');
            $student->city = $request->input('city');
            $student->address = $request->input('address');
            $student->nationality_id = $request->input('nationality_id');

            // Public profile toggle (checked => 1, unchecked => 0)
            // إذا لم يُرسل الحقل فهذا يعني أن السويتش مغلق
            $student->is_profile_public = $request->has('is_profile_public');

            // Handle photo upload
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');

                Log::info('StudentProfileController: Photo upload started', [
                    'student_id' => $student->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'file_mime' => $file->getMimeType(),
                    'file_path' => $file->getRealPath(),
                ]);

                // Validate file size (2MB max)
                if ($file->getSize() > 2 * 1024 * 1024) {
                    throw new \Exception('حجم الصورة كبير جداً. الحد الأقصى: 2MB');
                }

                // Validate file type
                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($file->getMimeType(), $allowedMimes)) {
                    throw new \Exception('صيغة الصورة غير مدعومة. الصيغ المدعومة: JPG, PNG, GIF, WebP');
                }

                // Delete old photo if exists
                if ($student->photo) {
                    try {
                        if ($this->storageHelper->fileExists('public', $student->photo)) {
                            $this->storageHelper->deleteFile('public', $student->photo);
                            Log::info('StudentProfileController: Old photo deleted', [
                                'old_photo_path' => $student->photo,
                            ]);
                        }
                    } catch (\Exception $deleteException) {
                        Log::warning('StudentProfileController: Failed to delete old photo', [
                            'old_photo_path' => $student->photo,
                            'error' => $deleteException->getMessage(),
                        ]);
                    }
                }

                // Generate unique filename
                $extension = $file->getClientOriginalExtension();
                $fileName = 'student_' . $student->id . '_' . time() . '.' . $extension;
                $photoPath = 'profile-photos/' . $fileName;

                Log::info('StudentProfileController: Attempting S3 upload', [
                    'photo_path' => $photoPath,
                    'file_size' => $file->getSize(),
                ]);

                // Upload to S3 via dynamic storage
                try {
                    $storage = $this->storageHelper->getDisk('public');
                    $fileContent = file_get_contents($file->getRealPath());
                    
                    if ($fileContent === false) {
                        throw new \Exception('فشل في قراءة ملف الصورة');
                    }

                    Log::info('StudentProfileController: File content read successfully', [
                        'content_size' => strlen($fileContent),
                    ]);

                    $result = $storage->put($photoPath, $fileContent, 'public');

                    Log::info('StudentProfileController: Storage put result', [
                        'result' => $result,
                    ]);

                    if ($result) {
                        Log::info('StudentProfileController: Photo uploaded successfully to S3', [
                            'photo_path' => $photoPath,
                        ]);

                        // Verify file exists
                        if ($this->storageHelper->fileExists('public', $photoPath)) {
                            $student->photo = $photoPath;
                            Log::info('StudentProfileController: Photo path saved to student model', [
                                'photo_path' => $photoPath,
                            ]);

                            // Track storage usage
                            try {
                                $mapping = \App\Models\StorageDiskMapping::where('disk_name', 'public')
                                    ->where('is_active', true)
                                    ->first();
                                
                                if ($mapping && $mapping->primaryStorage) {
                                    $analyticsService = app(\App\Services\Storage\AppStorageAnalyticsService::class);
                                    $analyticsService->trackStorageUsage($mapping->primaryStorage, $file->getSize(), 'profile-photo');
                                    $analyticsService->trackBandwidth($mapping->primaryStorage, 'upload', $file->getSize(), 'profile-photo');
                                }
                            } catch (\Exception $trackingException) {
                                Log::warning('StudentProfileController: Failed to track storage usage', [
                                    'error' => $trackingException->getMessage(),
                                ]);
                            }
                        } else {
                            Log::error('StudentProfileController: File does not exist after upload', [
                                'photo_path' => $photoPath,
                            ]);
                            throw new \Exception('تم رفع الصورة لكن الملف غير موجود في التخزين');
                        }
                    } else {
                        Log::error('StudentProfileController: Storage put returned false', [
                            'photo_path' => $photoPath,
                        ]);
                        throw new \Exception('فشل في رفع الصورة إلى التخزين السحابي');
                    }
                } catch (\Exception $uploadException) {
                    Log::error('StudentProfileController: S3 upload failed', [
                        'error' => $uploadException->getMessage(),
                        'trace' => $uploadException->getTraceAsString(),
                    ]);
                    throw new \Exception('فشل في رفع الصورة: ' . $uploadException->getMessage());
                }
            } else {
                Log::info('StudentProfileController: No photo file in request', [
                    'has_file' => $request->hasFile('photo'),
                    'all_files' => $request->allFiles(),
                ]);
            }

            $student->save();

            DB::commit();

            return redirect()->route('student.profile.index')
                ->with('success', 'تم تحديث الملف الشخصي بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('StudentProfileController: Update failed', [
                'student_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء تحديث الملف الشخصي: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Change the student's password.
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $student = auth()->user();

            // Verify current password
            if (!Hash::check($request->current_password, $student->password)) {
                return redirect()->back()
                    ->with('error', 'كلمة المرور الحالية غير صحيحة')
                    ->withInput();
            }

            // Update password
            $student->password = Hash::make($request->new_password);
            $student->save();

            return redirect()->route('student.profile.index')
                ->with('success', 'تم تغيير كلمة المرور بنجاح');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء تغيير كلمة المرور: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete the student's profile photo.
     */
    public function deletePhoto()
    {
        try {
            $student = auth()->user();

            if ($student->photo && $this->storageHelper->fileExists('public', $student->photo)) {
                $this->storageHelper->deleteFile('public', $student->photo);
                $student->photo = null;
                $student->save();

                return redirect()->back()
                    ->with('success', 'تم حذف الصورة الشخصية بنجاح');
            }

            return redirect()->back()
                ->with('info', 'لا توجد صورة شخصية لحذفها');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حذف الصورة: ' . $e->getMessage());
        }
    }
}
