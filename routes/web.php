<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\SettingsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function (Request $request) {
        $user = $request->user();

        return Inertia::render('Dashboard', [
            'gmCampaigns' => $user->gmCampaigns()
                ->with(['system:id,name,code', 'gm:id,username,avatar_url'])
                ->withCount(['players', 'characters'])
                ->latest()
                ->get(),
            'playerCampaigns' => $user->joinedCampaigns()
                ->with(['system:id,name,code', 'gm:id,username,avatar_url'])
                ->withCount(['players', 'characters'])
                ->latest('campaign_user.created_at')
                ->get(),
        ]);
    })->name('dashboard');

    Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
    Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
    Route::post('/campaigns/join', [CampaignController::class, 'join'])->name('campaigns.join');
    Route::get('/campaigns/{campaign}', [CampaignController::class, 'show'])->name('campaigns.show');
    Route::patch('/campaigns/{campaign}', [CampaignController::class, 'update'])->name('campaigns.update');
    Route::delete('/campaigns/{campaign}', [CampaignController::class, 'destroy'])->name('campaigns.destroy');
    Route::post('/campaigns/{campaign}/characters', [CampaignController::class, 'assignCharacter'])->name('campaigns.characters.assign');

    Route::get('/characters', [CharacterController::class, 'index'])->name('characters.index');
    Route::post('/characters', [CharacterController::class, 'store'])->name('characters.store');
    Route::get('/characters/{character}', [CharacterController::class, 'show'])->name('characters.show');
    Route::patch('/characters/{character}', [CharacterController::class, 'update'])->name('characters.update');
    Route::delete('/characters/{character}', [CharacterController::class, 'destroy'])->name('characters.destroy');

    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::patch('/settings/theme', [SettingsController::class, 'updateTheme'])->name('settings.theme.update');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');
    Route::delete('/settings', [SettingsController::class, 'destroy'])->name('settings.destroy');
});
