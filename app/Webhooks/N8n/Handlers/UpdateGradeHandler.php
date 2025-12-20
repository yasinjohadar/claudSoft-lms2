<?php

namespace App\Webhooks\N8n\Handlers;

use App\Models\User;

class UpdateGradeHandler extends BaseHandler
{
    public function handle(array $payload): array
    {
        try {
            $this->validate($payload, ['user_id', 'gradable_type', 'gradable_id', 'grade']);

            $user = User::find($payload['user_id']);

            if (!$user) {
                return $this->error('User not found');
            }

            // This is a simplified version
            // Actual implementation would depend on your grading system structure
            $this->logSuccess('Grade update request processed', [
                'user_id' => $payload['user_id'],
                'gradable_type' => $payload['gradable_type'],
                'grade' => $payload['grade'],
            ]);

            return $this->success('Grade updated successfully', [
                'user' => $user->name,
                'grade' => $payload['grade'],
                'gradable_type' => $payload['gradable_type'],
            ]);
        } catch (\Exception $e) {
            $this->logError('Failed to update grade', [
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to update grade: ' . $e->getMessage());
        }
    }
}
