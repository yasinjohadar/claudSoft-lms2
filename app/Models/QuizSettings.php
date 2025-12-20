<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizSettings extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'quiz_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'quiz_id',
        'require_password',
        'quiz_password',
        'browser_security',
        'allow_navigation',
        'navigation_method',
        'show_question_numbers',
        'questions_per_page',
        'show_timer',
        'auto_submit',
        'allow_pause',
        'show_progress_bar',
        'enable_calculator',
        'decimal_places',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'require_password' => 'boolean',
        'allow_navigation' => 'boolean',
        'show_question_numbers' => 'boolean',
        'questions_per_page' => 'integer',
        'show_timer' => 'boolean',
        'auto_submit' => 'boolean',
        'allow_pause' => 'boolean',
        'show_progress_bar' => 'boolean',
        'enable_calculator' => 'boolean',
        'decimal_places' => 'integer',
    ];

    /**
     * The attributes that should be hidden.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'quiz_password',
    ];

    /**
     * Get the quiz that owns the settings.
     */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    /**
     * Check if password is required.
     */
    public function requiresPassword(): bool
    {
        return $this->require_password && !empty($this->quiz_password);
    }

    /**
     * Verify quiz password.
     */
    public function verifyPassword(string $password): bool
    {
        if (!$this->requiresPassword()) {
            return true;
        }

        return $password === $this->quiz_password;
    }

    /**
     * Check if navigation is allowed.
     */
    public function allowsNavigation(): bool
    {
        return $this->allow_navigation;
    }

    /**
     * Check if navigation is sequential.
     */
    public function isSequentialNavigation(): bool
    {
        return $this->navigation_method === 'sequential';
    }

    /**
     * Check if navigation is free.
     */
    public function isFreeNavigation(): bool
    {
        return $this->navigation_method === 'free';
    }

    /**
     * Check if browser security is enabled.
     */
    public function hasBrowserSecurity(): bool
    {
        return $this->browser_security !== 'none';
    }

    /**
     * Get browser security mode.
     */
    public function getBrowserSecurityMode(): string
    {
        return $this->browser_security ?? 'none';
    }

    /**
     * Check if calculator is enabled.
     */
    public function hasCalculator(): bool
    {
        return $this->enable_calculator;
    }

    /**
     * Check if timer should be shown.
     */
    public function showsTimer(): bool
    {
        return $this->show_timer;
    }

    /**
     * Check if auto-submit is enabled.
     */
    public function hasAutoSubmit(): bool
    {
        return $this->auto_submit;
    }

    /**
     * Check if pause is allowed.
     */
    public function allowsPause(): bool
    {
        return $this->allow_pause;
    }

    /**
     * Check if progress bar should be shown.
     */
    public function showsProgressBar(): bool
    {
        return $this->show_progress_bar;
    }

    /**
     * Check if question numbers should be shown.
     */
    public function showsQuestionNumbers(): bool
    {
        return $this->show_question_numbers;
    }

    /**
     * Get questions per page.
     */
    public function getQuestionsPerPage(): int
    {
        return $this->questions_per_page ?? 1;
    }

    /**
     * Get decimal places for numerical questions.
     */
    public function getDecimalPlaces(): int
    {
        return $this->decimal_places ?? 2;
    }
}
