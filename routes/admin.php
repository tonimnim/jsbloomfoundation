<?php

use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\LogsController;
use App\Http\Controllers\Admin\PostsController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\NewsletterController as AdminNewsletterController;
use App\Http\Controllers\Admin\ContactsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DonationsController;
use App\Http\Controllers\Admin\NotificationsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "admin" middleware group.
|
*/

// Admin routes that require authentication and admin role
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationsController::class, 'index'])->name('index');
        Route::post('/{id}/read', [NotificationsController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotificationsController::class, 'markAllAsRead'])->name('read-all');
    });

    // Profile Management
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [AdminProfileController::class, 'show'])->name('show');
        Route::get('/edit', [AdminProfileController::class, 'edit'])->name('edit');
        Route::put('/', [AdminProfileController::class, 'update'])->name('update');
        Route::put('/password', [AdminProfileController::class, 'updatePassword'])->name('password.update');
        Route::delete('/profile-picture', [AdminProfileController::class, 'deleteProfilePicture'])->name('profile-picture.delete');
    });

    // Settings Management
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::put('/{section}', [SettingsController::class, 'update'])->name('update');
    });

    // Gallery Management
    Route::prefix('gallery')->name('gallery.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\GalleryController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\GalleryController::class, 'store'])->name('store');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\GalleryController::class, 'destroy'])->name('destroy');
    });

    // Placeholder routes for navigation items (will be implemented later)
    Route::get('/pages', function () {
        return inertia('Admin/Pages/Index');
    })->name('pages.index');

    // Blog Posts Management
    Route::prefix('posts')->name('posts.')->group(function () {
        Route::get('/', [PostsController::class, 'index'])->name('index');
        Route::get('/create', [PostsController::class, 'create'])->name('create');
        Route::post('/', [PostsController::class, 'store'])->name('store');
        Route::get('/{post}/edit', [PostsController::class, 'edit'])->name('edit');
        Route::put('/{post}', [PostsController::class, 'update'])->name('update');
        Route::delete('/{post}', [PostsController::class, 'destroy'])->name('destroy');
        Route::put('/{post}/toggle-featured', [PostsController::class, 'toggleFeatured'])->name('toggle-featured');
    });

    // Media API routes (used by posts, but no standalone media library)
    Route::prefix('media')->name('media.')->group(function () {
        Route::post('/upload', [MediaController::class, 'upload'])->name('upload');
        Route::get('/all', [MediaController::class, 'all'])->name('all');
    });

    Route::get('/team', function () {
        return inertia('Admin/Team/Index');
    })->name('team.index');

    Route::get('/projects', function () {
        return inertia('Admin/Projects/Index');
    })->name('projects.index');

    Route::get('/metrics', function () {
        return inertia('Admin/Metrics/Index');
    })->name('metrics.index');

    Route::get('/stories', function () {
        return inertia('Admin/Stories/Index');
    })->name('stories.index');

    // Donations Management
    Route::prefix('donations')->name('donations.')->group(function () {
        Route::get('/', [DonationsController::class, 'index'])->name('index');
        Route::get('/{id}', [DonationsController::class, 'show'])->name('show');
        Route::get('/{id}/receipt', [DonationsController::class, 'receipt'])->name('receipt');
        Route::get('/export/data', [DonationsController::class, 'export'])->name('export');
    });



    // User Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UsersController::class, 'index'])->name('index');
        Route::get('/{user}', [UsersController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UsersController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UsersController::class, 'update'])->name('update');
        Route::put('/{user}/toggle-status', [UsersController::class, 'toggleStatus'])->name('toggle-status');
        Route::put('/{user}/verify-email', [UsersController::class, 'verifyEmail'])->name('verify-email');
        Route::delete('/{user}', [UsersController::class, 'destroy'])->name('destroy');
    });

    Route::get('/applications', function () {
        return inertia('Admin/Applications/Index');
    })->name('applications.index');

    Route::get('/jobs', function () {
        return inertia('Admin/Jobs/Index');
    })->name('jobs.index');

    // Newsletter Management
    Route::prefix('newsletter')->name('newsletter.')->group(function () {
        Route::get('/', [AdminNewsletterController::class, 'index'])->name('index');
        Route::get('/export', [AdminNewsletterController::class, 'export'])->name('export');
        Route::delete('/{id}', [AdminNewsletterController::class, 'destroy'])->name('destroy');
        Route::put('/{id}/toggle-status', [AdminNewsletterController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Contact Messages Management
    Route::prefix('contacts')->name('contacts.')->group(function () {
        Route::get('/', [ContactsController::class, 'index'])->name('index');
        Route::get('/{id}', [ContactsController::class, 'show'])->name('show');
        Route::put('/{id}/toggle-read', [ContactsController::class, 'toggleRead'])->name('toggle-read');
        Route::post('/{id}/reply', [ContactsController::class, 'reply'])->name('reply');
        Route::delete('/{id}', [ContactsController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-delete', [ContactsController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/bulk-mark-read', [ContactsController::class, 'bulkMarkRead'])->name('bulk-mark-read');
    });

    Route::get('/campaigns', function () {
        return inertia('Admin/Campaigns/Index');
    })->name('campaigns.index');

    Route::get('/comments', function () {
        return inertia('Admin/Comments/Index');
    })->name('comments.index');

    Route::get('/analytics', function () {
        return inertia('Admin/Analytics/Index');
    })->name('analytics.index');

    Route::get('/donation-analytics', function () {
        return inertia('Admin/DonationAnalytics/Index');
    })->name('donation-analytics.index');

    Route::get('/custom-reports', function () {
        return inertia('Admin/CustomReports/Index');
    })->name('custom-reports.index');


    // Admin Users Management
    Route::prefix('admin-users')->name('admin-users.')->group(function () {
        Route::get('/', [AdminUsersController::class, 'index'])->name('index');
        Route::get('/create', [AdminUsersController::class, 'create'])->name('create');
        Route::post('/', [AdminUsersController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [AdminUsersController::class, 'edit'])->name('edit');
        Route::put('/{user}', [AdminUsersController::class, 'update'])->name('update');
        Route::put('/{user}/toggle-status', [AdminUsersController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{user}', [AdminUsersController::class, 'destroy'])->name('destroy');
    });

    // System Logs
    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/', [LogsController::class, 'index'])->name('index');
        Route::delete('/clear-old', [LogsController::class, 'clearOldLogs'])->name('clear-old');
    });
});