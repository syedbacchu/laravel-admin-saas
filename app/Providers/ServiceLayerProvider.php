<?php

namespace App\Providers;

use App\Http\Services\Audit\AuditRepository;
use App\Http\Services\Audit\AuditRepositoryInterface;
use App\Http\Services\Audit\AuditService;
use App\Http\Services\Audit\AuditServiceInterface;
use App\Http\Services\Contact\ContactRepository;
use App\Http\Services\Contact\ContactRepositoryInterface;
use App\Http\Services\Contact\ContactService;
use App\Http\Services\Contact\ContactServiceInterface;
use App\Http\Services\Component\ComponentRepository;
use App\Http\Services\Component\ComponentRepositoryInterface;
use App\Http\Services\Component\ComponentService;
use App\Http\Services\Component\ComponentServiceInterface;
use App\Http\Services\ComponentField\ComponentFieldRepository;
use App\Http\Services\ComponentField\ComponentFieldRepositoryInterface;
use App\Http\Services\ComponentField\ComponentFieldService;
use App\Http\Services\ComponentField\ComponentFieldServiceInterface;
use App\Http\Services\CustomField\CustomFieldRepository;
use App\Http\Services\CustomField\CustomFieldRepositoryInterface;
use App\Http\Services\CustomField\CustomFieldService;
use App\Http\Services\CustomField\CustomFieldServiceInterface;
use App\Http\Services\DatabaseBackup\DatabaseBackupRepository;
use App\Http\Services\DatabaseBackup\DatabaseBackupRepositoryInterface;
use App\Http\Services\DatabaseBackup\DatabaseBackupService;
use App\Http\Services\DatabaseBackup\DatabaseBackupServiceInterface;
use App\Http\Services\District\DistrictRepository;
use App\Http\Services\District\DistrictRepositoryInterface;
use App\Http\Services\District\DistrictService;
use App\Http\Services\District\DistrictServiceInterface;
use App\Http\Services\Division\DivisionRepository;
use App\Http\Services\Division\DivisionRepositoryInterface;
use App\Http\Services\Division\DivisionService;
use App\Http\Services\Division\DivisionServiceInterface;
use App\Http\Services\Subscriber\SubscriberRepository;
use App\Http\Services\Subscriber\SubscriberRepositoryInterface;
use App\Http\Services\Subscriber\SubscriberService;
use App\Http\Services\Subscriber\SubscriberServiceInterface;
use App\Http\Services\TenantCustomer\TenantCustomerRepository;
use App\Http\Services\TenantCustomer\TenantCustomerRepositoryInterface;
use App\Http\Services\TenantCustomer\TenantCustomerService;
use App\Http\Services\TenantCustomer\TenantCustomerServiceInterface;
use App\Http\Services\TenantMigrationLog\TenantMigrationLogRepository;
use App\Http\Services\TenantMigrationLog\TenantMigrationLogRepositoryInterface;
use App\Http\Services\TenantMigrationLog\TenantMigrationLogService;
use App\Http\Services\TenantMigrationLog\TenantMigrationLogServiceInterface;
use App\Http\Services\TenantOffice\TenantOfficeRepository;
use App\Http\Services\TenantOffice\TenantOfficeRepositoryInterface;
use App\Http\Services\TenantOffice\TenantOfficeService;
use App\Http\Services\TenantOffice\TenantOfficeServiceInterface;
use App\Http\Services\TenantRoutePricing\TenantRoutePricingRepository;
use App\Http\Services\TenantRoutePricing\TenantRoutePricingRepositoryInterface;
use App\Http\Services\TenantRoutePricing\TenantRoutePricingService;
use App\Http\Services\TenantRoutePricing\TenantRoutePricingServiceInterface;
use App\Http\Services\TenantDailyOfficeExpense\TenantDailyOfficeExpenseRepository;
use App\Http\Services\TenantDailyOfficeExpense\TenantDailyOfficeExpenseRepositoryInterface;
use App\Http\Services\TenantDailyOfficeExpense\TenantDailyOfficeExpenseService;
use App\Http\Services\TenantDailyOfficeExpense\TenantDailyOfficeExpenseServiceInterface;
use App\Http\Services\TenantSalaryExpense\TenantSalaryExpenseRepository;
use App\Http\Services\TenantSalaryExpense\TenantSalaryExpenseRepositoryInterface;
use App\Http\Services\TenantSalaryExpense\TenantSalaryExpenseService;
use App\Http\Services\TenantSalaryExpense\TenantSalaryExpenseServiceInterface;
use App\Http\Services\TenantEmployee\TenantEmployeeRepository;
use App\Http\Services\TenantEmployee\TenantEmployeeRepositoryInterface;
use App\Http\Services\TenantEmployee\TenantEmployeeService;
use App\Http\Services\TenantEmployee\TenantEmployeeServiceInterface;
use App\Http\Services\TenantPayrollAdvanceSalary\TenantPayrollAdvanceSalaryRepository;
use App\Http\Services\TenantPayrollAdvanceSalary\TenantPayrollAdvanceSalaryRepositoryInterface;
use App\Http\Services\TenantPayrollAdvanceSalary\TenantPayrollAdvanceSalaryService;
use App\Http\Services\TenantPayrollAdvanceSalary\TenantPayrollAdvanceSalaryServiceInterface;
use App\Http\Services\TenantPayrollAttendance\TenantPayrollAttendanceRepository;
use App\Http\Services\TenantPayrollAttendance\TenantPayrollAttendanceRepositoryInterface;
use App\Http\Services\TenantPayrollAttendance\TenantPayrollAttendanceService;
use App\Http\Services\TenantPayrollAttendance\TenantPayrollAttendanceServiceInterface;
use App\Http\Services\TenantPayrollBonus\TenantPayrollBonusRepository;
use App\Http\Services\TenantPayrollBonus\TenantPayrollBonusRepositoryInterface;
use App\Http\Services\TenantPayrollBonus\TenantPayrollBonusService;
use App\Http\Services\TenantPayrollBonus\TenantPayrollBonusServiceInterface;
use App\Http\Services\TenantPayrollGenerateSalary\TenantPayrollGenerateSalaryRepository;
use App\Http\Services\TenantPayrollGenerateSalary\TenantPayrollGenerateSalaryRepositoryInterface;
use App\Http\Services\TenantPayrollGenerateSalary\TenantPayrollGenerateSalaryService;
use App\Http\Services\TenantPayrollGenerateSalary\TenantPayrollGenerateSalaryServiceInterface;
use App\Http\Services\TenantPayrollLoan\TenantPayrollLoanRepository;
use App\Http\Services\TenantPayrollLoan\TenantPayrollLoanRepositoryInterface;
use App\Http\Services\TenantPayrollLoan\TenantPayrollLoanService;
use App\Http\Services\TenantPayrollLoan\TenantPayrollLoanServiceInterface;
use App\Http\Services\TenantPayrollSalaryPayment\TenantPayrollSalaryPaymentRepository;
use App\Http\Services\TenantPayrollSalaryPayment\TenantPayrollSalaryPaymentRepositoryInterface;
use App\Http\Services\TenantPayrollSalaryPayment\TenantPayrollSalaryPaymentService;
use App\Http\Services\TenantPayrollSalaryPayment\TenantPayrollSalaryPaymentServiceInterface;
use App\Http\Services\TenantFile\TenantFileRepository;
use App\Http\Services\TenantFile\TenantFileRepositoryInterface;
use App\Http\Services\TenantFile\TenantFileService;
use App\Http\Services\TenantFile\TenantFileServiceInterface;
use App\Http\Services\TenantSetting\TenantSettingRepository;
use App\Http\Services\TenantSetting\TenantSettingRepositoryInterface;
use App\Http\Services\TenantSetting\TenantSettingService;
use App\Http\Services\TenantSetting\TenantSettingServiceInterface;
use App\Http\Services\Testimonial\TestimonialRepository;
use App\Http\Services\Testimonial\TestimonialRepositoryInterface;
use App\Http\Services\Testimonial\TestimonialService;
use App\Http\Services\Testimonial\TestimonialServiceInterface;
use App\Http\Services\Thana\ThanaRepository;
use App\Http\Services\Thana\ThanaRepositoryInterface;
use App\Http\Services\Thana\ThanaService;
use App\Http\Services\Thana\ThanaServiceInterface;
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
use App\Http\Services\Billing\FeatureRepository;
use App\Http\Services\Billing\FeatureRepositoryInterface;
use App\Http\Services\Billing\FeatureService;
use App\Http\Services\Billing\FeatureServiceInterface;
use App\Http\Services\Billing\PlanRepository;
use App\Http\Services\Billing\PlanRepositoryInterface;
use App\Http\Services\Billing\PlanService;
use App\Http\Services\Billing\PlanServiceInterface;
use App\Http\Services\Billing\PaymentMethodRepository;
use App\Http\Services\Billing\PaymentMethodRepositoryInterface;
use App\Http\Services\Billing\PaymentMethodService;
use App\Http\Services\Billing\PaymentMethodServiceInterface;
use App\Http\Services\Billing\SubscriptionRepository;
use App\Http\Services\Billing\SubscriptionRepositoryInterface;
use App\Http\Services\Billing\SubscriptionService;
use App\Http\Services\Billing\SubscriptionServiceInterface;
use App\Http\Services\Billing\SubscriptionPaymentRepository;
use App\Http\Services\Billing\SubscriptionPaymentRepositoryInterface;
use App\Http\Services\Billing\SubscriptionPaymentService;
use App\Http\Services\Billing\SubscriptionPaymentServiceInterface;
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
use App\Http\Services\Language\LanguageRepository;
use App\Http\Services\Language\LanguageRepositoryInterface;
use App\Http\Services\Language\LanguageService;
use App\Http\Services\Language\LanguageServiceInterface;
use App\Http\Services\User\UserRepository;
use App\Http\Services\User\UserRepositoryInterface;
use App\Http\Services\User\UserService;
use App\Http\Services\User\UserServiceInterface;
use App\Http\Services\Tenant\TenantRepository;
use App\Http\Services\Tenant\TenantRepositoryInterface;
use App\Http\Services\Tenant\TenantService;
use App\Http\Services\Tenant\TenantServiceInterface;
use App\Http\Services\TenantApi\TenantApiRepository;
use App\Http\Services\TenantApi\TenantApiRepositoryInterface;
use App\Http\Services\TenantApi\TenantApiService;
use App\Http\Services\TenantApi\TenantApiServiceInterface;
use App\Http\Services\Tenant\TenantProvisionService;
use App\Http\Services\Tenant\TenantProvisionServiceInterface;
use App\Http\Services\TenantStaff\TenantStaffRepository;
use App\Http\Services\TenantStaff\TenantStaffRepositoryInterface;
use App\Http\Services\TenantStaff\TenantStaffService;
use App\Http\Services\TenantStaff\TenantStaffServiceInterface;
use App\Http\Services\TenantStaff\StaffFeatureRepositoryInterface;
use App\Http\Services\TenantStaff\StaffFeatureRepository;
use App\Http\Services\PricingPlan\PricingPlanRepository;
use App\Http\Services\PricingPlan\PricingPlanRepositoryInterface;
use App\Http\Services\PricingPlan\PricingPlanService;
use App\Http\Services\PricingPlan\PricingPlanServiceInterface;
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

        $this->app->bind(DatabaseBackupRepositoryInterface::class, DatabaseBackupRepository::class);
        $this->app->bind(DatabaseBackupServiceInterface::class, DatabaseBackupService::class);

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

        $this->app->bind(LanguageRepositoryInterface::class, LanguageRepository::class);
        $this->app->bind(LanguageServiceInterface::class, LanguageService::class);

        $this->app->bind(FeatureRepositoryInterface::class, FeatureRepository::class);
        $this->app->bind(FeatureServiceInterface::class, FeatureService::class);

        $this->app->bind(PlanRepositoryInterface::class, PlanRepository::class);
        $this->app->bind(PlanServiceInterface::class, PlanService::class);

        $this->app->bind(SubscriptionRepositoryInterface::class, SubscriptionRepository::class);
        $this->app->bind(SubscriptionServiceInterface::class, SubscriptionService::class);

        $this->app->bind(PaymentMethodRepositoryInterface::class, PaymentMethodRepository::class);
        $this->app->bind(PaymentMethodServiceInterface::class, PaymentMethodService::class);

        $this->app->bind(SubscriptionPaymentRepositoryInterface::class, SubscriptionPaymentRepository::class);
        $this->app->bind(SubscriptionPaymentServiceInterface::class, SubscriptionPaymentService::class);

        $this->app->bind(TenantRepositoryInterface::class, TenantRepository::class);
        $this->app->bind(TenantServiceInterface::class, TenantService::class);
        $this->app->bind(TenantProvisionServiceInterface::class, TenantProvisionService::class);

        $this->app->bind(TenantApiRepositoryInterface::class, TenantApiRepository::class);
        $this->app->bind(TenantApiServiceInterface::class, TenantApiService::class);

        $this->app->bind(TenantStaffRepositoryInterface::class, TenantStaffRepository::class);
        $this->app->bind(TenantStaffServiceInterface::class, TenantStaffService::class);

        $this->app->bind(StaffFeatureRepositoryInterface::class, StaffFeatureRepository::class);

        $this->app->bind(TenantCustomerRepositoryInterface::class, TenantCustomerRepository::class);
        $this->app->bind(TenantCustomerServiceInterface::class, TenantCustomerService::class);

        $this->app->bind(TenantOfficeRepositoryInterface::class, TenantOfficeRepository::class);
        $this->app->bind(TenantOfficeServiceInterface::class, TenantOfficeService::class);

        $this->app->bind(TenantRoutePricingRepositoryInterface::class, TenantRoutePricingRepository::class);
        $this->app->bind(TenantRoutePricingServiceInterface::class, TenantRoutePricingService::class);

        $this->app->bind(TenantDailyOfficeExpenseRepositoryInterface::class, TenantDailyOfficeExpenseRepository::class);
        $this->app->bind(TenantDailyOfficeExpenseServiceInterface::class, TenantDailyOfficeExpenseService::class);

        $this->app->bind(TenantSalaryExpenseRepositoryInterface::class, TenantSalaryExpenseRepository::class);
        $this->app->bind(TenantSalaryExpenseServiceInterface::class, TenantSalaryExpenseService::class);

        $this->app->bind(TenantEmployeeRepositoryInterface::class, TenantEmployeeRepository::class);
        $this->app->bind(TenantEmployeeServiceInterface::class, TenantEmployeeService::class);

        $this->app->bind(TenantPayrollAttendanceRepositoryInterface::class, TenantPayrollAttendanceRepository::class);
        $this->app->bind(TenantPayrollAttendanceServiceInterface::class, TenantPayrollAttendanceService::class);

        $this->app->bind(TenantPayrollBonusRepositoryInterface::class, TenantPayrollBonusRepository::class);
        $this->app->bind(TenantPayrollBonusServiceInterface::class, TenantPayrollBonusService::class);

        $this->app->bind(TenantPayrollAdvanceSalaryRepositoryInterface::class, TenantPayrollAdvanceSalaryRepository::class);
        $this->app->bind(TenantPayrollAdvanceSalaryServiceInterface::class, TenantPayrollAdvanceSalaryService::class);

        $this->app->bind(TenantPayrollLoanRepositoryInterface::class, TenantPayrollLoanRepository::class);
        $this->app->bind(TenantPayrollLoanServiceInterface::class, TenantPayrollLoanService::class);

        $this->app->bind(TenantPayrollGenerateSalaryRepositoryInterface::class, TenantPayrollGenerateSalaryRepository::class);
        $this->app->bind(TenantPayrollGenerateSalaryServiceInterface::class, TenantPayrollGenerateSalaryService::class);

        $this->app->bind(TenantPayrollSalaryPaymentRepositoryInterface::class, TenantPayrollSalaryPaymentRepository::class);
        $this->app->bind(TenantPayrollSalaryPaymentServiceInterface::class, TenantPayrollSalaryPaymentService::class);

        $this->app->bind(TenantFileRepositoryInterface::class, TenantFileRepository::class);
        $this->app->bind(TenantFileServiceInterface::class, TenantFileService::class);

        $this->app->bind(TenantSettingRepositoryInterface::class, TenantSettingRepository::class);
        $this->app->bind(TenantSettingServiceInterface::class, TenantSettingService::class);

        $this->app->bind(PricingPlanRepositoryInterface::class, PricingPlanRepository::class);
        $this->app->bind(PricingPlanServiceInterface::class, PricingPlanService::class);

        $this->app->bind(DivisionRepositoryInterface::class, DivisionRepository::class);
        $this->app->bind(DivisionServiceInterface::class, DivisionService::class);

        $this->app->bind(DistrictRepositoryInterface::class, DistrictRepository::class);
        $this->app->bind(DistrictServiceInterface::class, DistrictService::class);

        $this->app->bind(ThanaRepositoryInterface::class, ThanaRepository::class);
        $this->app->bind(ThanaServiceInterface::class, ThanaService::class);

        $this->app->bind(TenantMigrationLogRepositoryInterface::class, TenantMigrationLogRepository::class);
        $this->app->bind(TenantMigrationLogServiceInterface::class, TenantMigrationLogService::class);

        $this->app->bind(TestimonialRepositoryInterface::class, TestimonialRepository::class);
        $this->app->bind(TestimonialServiceInterface::class, TestimonialService::class);

        $this->app->bind(SubscriberRepositoryInterface::class, SubscriberRepository::class);
        $this->app->bind(SubscriberServiceInterface::class, SubscriberService::class);

        $this->app->bind(ContactRepositoryInterface::class, ContactRepository::class);
        $this->app->bind(ContactServiceInterface::class, ContactService::class);

        // Component & ComponentField Services
        $this->app->bind(ComponentRepositoryInterface::class, ComponentRepository::class);
        $this->app->bind(ComponentServiceInterface::class, ComponentService::class);

        $this->app->bind(ComponentFieldRepositoryInterface::class, ComponentFieldRepository::class);
        $this->app->bind(ComponentFieldServiceInterface::class, ComponentFieldService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
