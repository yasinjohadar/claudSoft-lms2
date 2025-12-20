<?php

namespace App\Webhooks\N8n\Handlers;

use App\Models\User;

class UpdateUserHandler extends BaseHandler
{
    /**
     * Handle updating an existing user
     */
    public function handle(array $payload): array
    {
        try {
            // Validate required fields
            $this->validate($payload, ['user_id']);

            // Find user
            $user = User::find($payload['user_id']);

            if (!$user) {
                return $this->error('User not found', [
                    'user_id' => $payload['user_id'],
                ]);
            }

            // Prepare update data
            $updateData = [];

            if (isset($payload['name'])) {
                $updateData['name'] = $payload['name'];
            }

            if (isset($payload['email'])) {
                // Check if email is already taken by another user
                $emailExists = User::where('email', $payload['email'])
                    ->where('id', '!=', $user->id)
                    ->exists();

                if ($emailExists) {
                    return $this->error('Email is already taken by another user');
                }

                $updateData['email'] = $payload['email'];
            }

            if (isset($payload['phone'])) {
                $updateData['phone'] = $payload['phone'];
            }

            if (isset($payload['bio'])) {
                $updateData['bio'] = $payload['bio'];
            }

            // Update user
            $user->update($updateData);

            // Update role if provided
            if (isset($payload['role']) && !empty($payload['role'])) {
                $user->syncRoles([$payload['role']]);
            }

            $this->logSuccess('User updated successfully', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($updateData),
            ]);

            return $this->success('User updated successfully', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            $this->logError('Failed to update user', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return $this->error('Failed to update user: ' . $e->getMessage());
        }
    }
}
