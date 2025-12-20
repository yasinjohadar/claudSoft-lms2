<?php

namespace App\Webhooks\N8n\Handlers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateUserHandler extends BaseHandler
{
    /**
     * Handle creating a new user
     */
    public function handle(array $payload): array
    {
        try {
            // Validate required fields
            $this->validate($payload, ['name', 'email']);

            // Check if user already exists
            if (User::where('email', $payload['email'])->exists()) {
                return $this->error('User with this email already exists', [
                    'email' => $payload['email'],
                ]);
            }

            // Create the user
            $user = User::create([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'password' => Hash::make($payload['password'] ?? Str::random(16)),
                'phone' => $payload['phone'] ?? null,
                'bio' => $payload['bio'] ?? null,
            ]);

            // Assign role if provided
            if (isset($payload['role']) && !empty($payload['role'])) {
                $user->assignRole($payload['role']);
            }

            $this->logSuccess('User created successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return $this->success('User created successfully', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            $this->logError('Failed to create user', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return $this->error('Failed to create user: ' . $e->getMessage());
        }
    }
}
