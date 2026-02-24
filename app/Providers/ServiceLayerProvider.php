<?php

namespace App\Providers;

use App\Http\Services\Audit\AuditRepository;
use App\Http\Services\Audit\AuditRepositoryInterface;
use App\Http\Services\Audit\AuditService;
use App\Http\Services\Audit\AuditServiceInterface;
use App\Http\Services\CustomField\CustomFieldRepository;
use App\Http\Services\CustomField\CustomFieldRepositoryInterface;
use App\Http\Services\CustomField\CustomFieldService;
use App\Http\Services\CustomField\CustomFieldServiceInterface;
use App\Http\Services\Role\RoleRepository;
use App\Http\Services\Role\RoleRepositoryInterface;
use App\Http\Services\Role\RoleService;
use App\Http\Services\Role\RoleServiceInterface;
use App\Http\Services\Slider\SliderRepository;
use App\Http\Services\Slider\SliderRepositoryInterface;
use App\Http\Services\Slider\SliderService;
use App\Http\Services\Slider\SliderServiceInterface;
use App\Http\Services\PostCategory\PostCategoryRepository;
use App\Http\Services\PostCategory\PostCategoryRepositoryInterface;
use App\Http\Services\PostCategory\PostCategoryService;
use App\Http\Services\PostCategory\PostCategoryServiceInterface;
use App\Http\Services\Tag\TagRepository;
use App\Http\Services\Tag\TagRepositoryInterface;
use App\Http\Services\Tag\TagService;
use App\Http\Services\Tag\TagServiceInterface;
use App\Http\Services\Post\PostRepository;
use App\Http\Services\Post\PostRepositoryInterface;
use App\Http\Services\Post\PostService;
use App\Http\Services\Post\PostServiceInterface;
use App\Http\Services\PostComment\PostCommentRepository;
use App\Http\Services\PostComment\PostCommentRepositoryInterface;
use App\Http\Services\PostComment\PostCommentService;
use App\Http\Services\PostComment\PostCommentServiceInterface;
use App\Http\Services\FaqCategory\FaqCategoryRepository;
use App\Http\Services\FaqCategory\FaqCategoryRepositoryInterface;
use App\Http\Services\FaqCategory\FaqCategoryService;
use App\Http\Services\FaqCategory\FaqCategoryServiceInterface;
use App\Http\Services\Faq\FaqRepository;
use App\Http\Services\Faq\FaqRepositoryInterface;
use App\Http\Services\Faq\FaqService;
use App\Http\Services\Faq\FaqServiceInterface;
use App\Http\Services\User\UserRepository;
use App\Http\Services\User\UserRepositoryInterface;
use App\Http\Services\User\UserService;
use App\Http\Services\User\UserServiceInterface;
use Illuminate\Support\ServiceProvider;

class ServiceLayerProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {

        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);

        $this->app->bind(SliderRepositoryInterface::class, SliderRepository::class);
        $this->app->bind(SliderServiceInterface::class, SliderService::class);

        $this->app->bind(AuditRepositoryInterface::class, AuditRepository::class);
        $this->app->bind(AuditServiceInterface::class, AuditService::class);

        $this->app->bind(CustomFieldRepositoryInterface::class, CustomFieldRepository::class);
        $this->app->bind(CustomFieldServiceInterface::class, CustomFieldService::class);

        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(RoleServiceInterface::class, RoleService::class);

        $this->app->bind(FaqCategoryRepositoryInterface::class, FaqCategoryRepository::class);
        $this->app->bind(FaqCategoryServiceInterface::class, FaqCategoryService::class);

        $this->app->bind(FaqRepositoryInterface::class, FaqRepository::class);
        $this->app->bind(FaqServiceInterface::class, FaqService::class);

        $this->app->bind(PostCategoryRepositoryInterface::class, PostCategoryRepository::class);
        $this->app->bind(PostCategoryServiceInterface::class, PostCategoryService::class);

        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
        $this->app->bind(TagServiceInterface::class, TagService::class);

        $this->app->bind(PostRepositoryInterface::class, PostRepository::class);
        $this->app->bind(PostServiceInterface::class, PostService::class);

        $this->app->bind(PostCommentRepositoryInterface::class, PostCommentRepository::class);
        $this->app->bind(PostCommentServiceInterface::class, PostCommentService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
