<?php

use App\Http\Controllers\Admin\App\AppSliderController;
use App\Http\Controllers\Admin\Audit\AuditSettingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Faq\FaqCategoryController;
use App\Http\Controllers\Admin\Faq\FaqController;
use App\Http\Controllers\Admin\FileManager\FileManagerController;
use App\Http\Controllers\Admin\Role\RoleController;
use App\Http\Controllers\Admin\Settings\CustomFieldController;
use App\Http\Controllers\Admin\Settings\SettingsController;
use App\Http\Controllers\Admin\Settings\SettingFieldController;
use App\Http\Controllers\Admin\Post\PostCategoryController;
use App\Http\Controllers\Admin\Post\PostCommentController;
use App\Http\Controllers\Admin\Post\PostController;
use App\Http\Controllers\Admin\Post\TagController;
use App\Http\Controllers\Admin\User\UserController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;


Route::get('log', [Sdtech\LogViewerLaravel\Controllers\LogViewerLaravelController::class, 'index'])->name('errorLog');

Route::group(['middleware' => ['skip.permission','no.permission.sync']], function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('profile', [UserController::class, 'profile'])->name('profile');
    Route::get('edit-profile', [UserController::class, 'editProfile'])->name('editProfile');
    Route::post('update-profile', [UserController::class, 'updateProfile'])->name('updateProfile');
    Route::post('update-password', [UserController::class, 'updatePassword'])->name('updatePassword');
    Route::get('logout', [AuthController::class, 'logout'])->name('logout');
});


    // user management
    Route::resource('users', UserController::class)
        ->except(['destroy'])
        ->names([
        'index'   => 'user.list',
        'create'   => 'user.create',
        'edit'   => 'user.edit',
        'store'   => 'user.store',
        'update'  => 'user.update',
        'show' => 'user.show',
    ]);
    Route::group(['prefix' => 'users', 'as' => 'user.'], function () {
        Route::get('user-delete/{id}', [UserController::class, 'destroy'])->name('delete');
        Route::post('user-status', [UserController::class, 'status'])->name('status');
    });

    // General Setting
    Route::resource('fields', SettingFieldController::class)->names([
        'index'   => 'settings.fields.index',
        'create'   => 'settings.fields.create',
        'edit'   => 'settings.fields.edit',
        'store'   => 'settings.fields.store',
        'update'  => 'settings.fields.update',
        'destroy' => 'settings.fields.delete',
        'show' => 'settings.fields.show',
    ]);
    Route::group(['prefix' => 'settings', 'as' => 'settings.'], function () {
        Route::get('/', [SettingsController::class, 'index'])->name('generalSetting');
        Route::post('/settings/{group}', [SettingsController::class, 'update'])->name('update');
    });


    // App Slider
    Route::resource('app-slider', AppSliderController::class)
        ->except(['destroy'])
        ->names([
        'index'   => 'appSlider.list',
        'create'   => 'appSlider.create',
        'edit'   => 'appSlider.edit',
        'store'   => 'appSlider.store',
        'update'  => 'appSlider.update',
        'show' => 'appSlider.show',
    ]);
    Route::group(['prefix' => 'app-slider', 'as' => 'appSlider.'], function () {
        Route::get('app-slider-delete/{id}', [AppSliderController::class, 'destroy'])->name('delete');
        Route::post('publish', [AppSliderController::class, 'publish'])->name('publish');
    });


    // audit
    Route::group(['prefix' => 'audit', 'as' => 'audit.'], function () {
        Route::get('logs', [AuditSettingController::class, 'index'])->name('logs');
        Route::get('log/{id}', [AuditSettingController::class, 'show'])->name('log.show');
        Route::get('delete/{id}', [AuditSettingController::class, 'destroy'])->name('log.delete');
        Route::get('settings', [AuditSettingController::class, 'settings'])->name('settings');
        Route::post('update-model', [AuditSettingController::class, 'updateModel'])->name('updateModel');
        Route::get('reset-audit-model', [AuditSettingController::class, 'resetModel'])->name('resetModel');
    });

    // File Manager
    Route::group(['prefix' => 'file-manager', 'as' => 'fileManager.'], function () {
        Route::get('list', [FileManagerController::class, 'list'])->name('all')->middleware(['skip.permission','no.permission.sync']);
        Route::get('/', [FileManagerController::class, 'index'])->name('list')->middleware(['skip.permission','no.permission.sync']);
        Route::get('list-partial', [FileManagerController::class, 'listPartial'])->name('partial')->middleware(['skip.permission','no.permission.sync']);
        Route::get('create', [FileManagerController::class, 'create'])->name('create');
        Route::post('store-file', [FileManagerController::class, 'storeFile'])->name('storeFile')->middleware(['skip.permission','no.permission.sync']);
        Route::post('store', [FileManagerController::class, 'store'])->name('store');
        Route::get('delete/{id}', [FileManagerController::class, 'destroy'])->name('delete');
    });

    // custom fields
    Route::group(['prefix' => 'custom-fields', 'as' => 'customField.'], function () {
        Route::get('/', [CustomFieldController::class, 'index'])->name('index');
        Route::get('list', [CustomFieldController::class, 'listByModule'])->name('list')->middleware('skip.permission');
        Route::post('store', [CustomFieldController::class, 'store'])->name('store');
        Route::post('update', [CustomFieldController::class, 'update'])->name('update');
        Route::get('delete/{id}', [CustomFieldController::class, 'destroy'])->name('delete');
    });

    Route::resource('role', RoleController::class)
        ->except(['destroy'])
        ->names([
        'index'   => 'role.index',
        'create'   => 'role.create',
        'edit'   => 'role.edit',
        'store'   => 'role.store',
        'update'  => 'role.update',
        'show' => 'role.show',
    ]);

    Route::group([ 'as' => 'role.'], function () {
        Route::get('role-delete/{id}', [RoleController::class, 'delete'])->name('destroy');
        Route::post('role-publish', [RoleController::class, 'roleStatus'])->name('status');
        Route::get('role-sync-permission', [RoleController::class, 'syncPermission'])->name('syncPermission');
        Route::get('web-permission', [RoleController::class, 'webPermission'])->name('webPermission');
        Route::get('api-permission', [RoleController::class, 'apiPermission'])->name('apiPermission');
        Route::get('api-role', [RoleController::class, 'apiRole'])->name('apiRole');
        Route::get('delete-permission/{id}', [RoleController::class, 'deletePermission'])->name('deletePermission');
        Route::post('permission-publish', [RoleController::class, 'permissionPublish'])->name('permissionStatus');
    });

    // Faq Categories
    Route::resource('faq-categories', FaqCategoryController::class)->names([
        'index'   => 'faqCategory.list',
        'create'   => 'faqCategory.create',
        'edit'   => 'faqCategory.edit',
        'store'   => 'faqCategory.store',
        'update'  => 'faqCategory.update',
        'destroy' => 'faqCategory.delete',
        'show' => 'faqCategory.show',
    ]);
    Route::group(['prefix' => 'faq-categories', 'as' => 'faqCategory.'], function () {
        Route::post('publish', [FaqCategoryController::class, 'faqCategoryStatus'])->name('publish');
    });

    // Post Categories
    Route::resource('post-categories', PostCategoryController::class)
    ->except(['destroy'])
    ->names([
        'index'   => 'postCategory.list',
        'create'   => 'postCategory.create',
        'edit'   => 'postCategory.edit',
        'store'   => 'postCategory.store',
        'update'  => 'postCategory.update',
        'show' => 'postCategory.show',
    ]);
    Route::group(['prefix' => 'post-categories', 'as' => 'postCategory.'], function () {
        Route::get('post-category-delete/{id}', [PostCategoryController::class, 'destroy'])->name('delete');
        Route::post('publish', [PostCategoryController::class, 'postCategoryStatus'])->name('publish');
    });

    // Tags
    Route::resource('tags', TagController::class)
    ->except(['destroy'])
    ->names([
        'index'   => 'tag.list',
        'create'   => 'tag.create',
        'edit'   => 'tag.edit',
        'store'   => 'tag.store',
        'update'  => 'tag.update',
        'show' => 'tag.show',
    ]);
    Route::group(['prefix' => 'tags', 'as' => 'tag.'], function () {
        Route::get('tag-delete/{id}', [TagController::class, 'destroy'])->name('delete');
    });

    // Posts
    Route::resource('posts', PostController::class)
    ->except(['destroy'])
    ->names([
        'index'   => 'post.list',
        'create'   => 'post.create',
        'edit'   => 'post.edit',
        'store'   => 'post.store',
        'update'  => 'post.update',
        'show' => 'post.show',
    ]);
    Route::group(['prefix' => 'posts', 'as' => 'post.'], function () {
        Route::get('post-delete/{id}', [PostController::class, 'destroy'])->name('delete');
        Route::post('publish', [PostController::class, 'postStatus'])->name('publish');
    });

    // Post Comments
    Route::group(['prefix' => 'post-comments', 'as' => 'postComment.'], function () {
        Route::get('/', [PostCommentController::class, 'index'])->name('list');
        Route::get('reply/{id}', [PostCommentController::class, 'reply'])->name('reply');
        Route::post('reply/{id}', [PostCommentController::class, 'storeReply'])->name('replyStore');
        Route::get('approve/{id}', [PostCommentController::class, 'approve'])->name('approve');
        Route::get('decline/{id}', [PostCommentController::class, 'decline'])->name('decline');
        Route::get('delete/{id}', [PostCommentController::class, 'destroy'])->name('delete');
    });

    // Faq
    Route::resource('faq', FaqController::class)->names([
        'index'   => 'faq.list',
        'create'   => 'faq.create',
        'edit'   => 'faq.edit',
        'store'   => 'faq.store',
        'update'  => 'faq.update',
        'destroy' => 'faq.delete',
        'show' => 'faq.show',
    ]);
    Route::group(['prefix' => 'faq', 'as' => 'faq.'], function () {
        Route::post('publish', [FaqController::class, 'faqStatus'])->name('publish');
    });
