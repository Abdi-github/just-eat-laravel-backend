<?php

namespace App\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

// Interfaces
use App\Domain\Address\Repositories\AddressRepositoryInterface;
use App\Domain\Admin\Repositories\AnalyticsRepositoryInterface;
use App\Domain\Admin\Repositories\DashboardRepositoryInterface;
use App\Domain\Cuisine\Repositories\CuisineRepositoryInterface;
use App\Domain\Delivery\Repositories\DeliveryRepositoryInterface;
use App\Domain\Delivery\Repositories\DeliveryZoneRepositoryInterface;
use App\Domain\Location\Repositories\CantonRepositoryInterface;
use App\Domain\Location\Repositories\CityRepositoryInterface;
use App\Domain\Menu\Repositories\MenuCategoryRepositoryInterface;
use App\Domain\Menu\Repositories\MenuItemRepositoryInterface;
use App\Domain\Notification\Repositories\NotificationRepositoryInterface;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Payment\Repositories\PaymentTransactionRepositoryInterface;
use App\Domain\Promotion\Repositories\PromotionRepositoryInterface;
use App\Domain\Promotion\Repositories\StampCardRepositoryInterface;
use App\Domain\Restaurant\Repositories\BrandRepositoryInterface;
use App\Domain\Restaurant\Repositories\FavoriteRepositoryInterface;
use App\Domain\Restaurant\Repositories\OpeningHourRepositoryInterface;
use App\Domain\Restaurant\Repositories\RestaurantRepositoryInterface;
use App\Domain\Review\Repositories\ReviewRepositoryInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;

// Eloquent Implementations
use App\Infrastructure\Persistence\Eloquent\EloquentAddressRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentAnalyticsRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentBrandRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentCantonRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentCityRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentCuisineRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentDashboardRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentDeliveryRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentDeliveryZoneRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentFavoriteRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentMenuCategoryRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentMenuItemRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentNotificationRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentOpeningHourRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentOrderRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentPaymentTransactionRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentPromotionRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentRestaurantRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentReviewRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentStampCardRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentUserRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AddressRepositoryInterface::class, EloquentAddressRepository::class);
        $this->app->bind(AnalyticsRepositoryInterface::class, EloquentAnalyticsRepository::class);
        $this->app->bind(BrandRepositoryInterface::class, EloquentBrandRepository::class);
        $this->app->bind(CantonRepositoryInterface::class, EloquentCantonRepository::class);
        $this->app->bind(CityRepositoryInterface::class, EloquentCityRepository::class);
        $this->app->bind(CuisineRepositoryInterface::class, EloquentCuisineRepository::class);
        $this->app->bind(DashboardRepositoryInterface::class, EloquentDashboardRepository::class);
        $this->app->bind(DeliveryRepositoryInterface::class, EloquentDeliveryRepository::class);
        $this->app->bind(DeliveryZoneRepositoryInterface::class, EloquentDeliveryZoneRepository::class);
        $this->app->bind(FavoriteRepositoryInterface::class, EloquentFavoriteRepository::class);
        $this->app->bind(MenuCategoryRepositoryInterface::class, EloquentMenuCategoryRepository::class);
        $this->app->bind(MenuItemRepositoryInterface::class, EloquentMenuItemRepository::class);
        $this->app->bind(NotificationRepositoryInterface::class, EloquentNotificationRepository::class);
        $this->app->bind(OpeningHourRepositoryInterface::class, EloquentOpeningHourRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);
        $this->app->bind(PaymentTransactionRepositoryInterface::class, EloquentPaymentTransactionRepository::class);
        $this->app->bind(PromotionRepositoryInterface::class, EloquentPromotionRepository::class);
        $this->app->bind(RestaurantRepositoryInterface::class, EloquentRestaurantRepository::class);
        $this->app->bind(ReviewRepositoryInterface::class, EloquentReviewRepository::class);
        $this->app->bind(StampCardRepositoryInterface::class, EloquentStampCardRepository::class);
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
    }
}
