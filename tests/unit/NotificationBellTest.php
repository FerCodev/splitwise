<?php

/**
 * Tests de la campana de notificaciones en el topbar móvil y el script de contador.
 *
 * @internal
 */
final class NotificationBellTest extends \CodeIgniter\Test\CIUnitTestCase
{
    private string $navbar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->navbar = file_get_contents(APPPATH . 'Views/partials/_navbar.php');
    }

    public function testBellPresentInNavbar(): void
    {
        $this->assertStringContainsString('mobile-topbar-notif-bell', $this->navbar);
    }

    public function testBellLinksToNotificaciones(): void
    {
        $this->assertStringContainsString("base_url('notificaciones')", $this->navbar);
        $this->assertMatchesRegularExpression(
            '/href=.*notificaciones.*mobile-topbar-notif-bell/s',
            $this->navbar
        );
    }

    public function testTopbarBadgeHasUniqueId(): void
    {
        $this->assertStringContainsString('id="topbar-notif-badge"', $this->navbar);
        $this->assertStringContainsString('id="desktop-notif-badge"', $this->navbar);
        $this->assertStringContainsString('id="mobile-notif-badge"', $this->navbar);
    }

    public function testBellHasAriaLabel(): void
    {
        $this->assertMatchesRegularExpression(
            '/mobile-topbar-notif-bell[^>]*aria-label=/',
            $this->navbar
        );
    }

    public function testScriptUpdatesThreeBadges(): void
    {
        $this->assertStringContainsString('refreshNotificationCount', $this->navbar);
        $this->assertStringContainsString("getElementById('desktop-notif-badge')", $this->navbar);
        $this->assertStringContainsString("getElementById('mobile-notif-badge')", $this->navbar);
        $this->assertStringContainsString("getElementById('topbar-notif-badge')", $this->navbar);
    }

    public function testScriptLimitsTo99Plus(): void
    {
        $this->assertStringContainsString("99+", $this->navbar);
    }

    public function testScriptHidesBadgeWhenZero(): void
    {
        $this->assertStringContainsString("classList.add('d-none')", $this->navbar);
        $this->assertStringContainsString("classList.remove('d-none')", $this->navbar);
    }

    public function testScriptUpdatesAriaLabel(): void
    {
        $this->assertStringContainsString("setAttribute('aria-label'", $this->navbar);
    }

    public function testScriptPreventsDuplicateRequests(): void
    {
        $this->assertStringContainsString('fetching', $this->navbar);
    }

    public function testScriptListensVisibilityChange(): void
    {
        $this->assertStringContainsString('visibilitychange', $this->navbar);
    }

    public function testScriptListensWindowFocus(): void
    {
        $this->assertStringContainsString("addEventListener('focus'", $this->navbar);
    }

    public function testScriptListensServiceWorkerMessage(): void
    {
        $this->assertStringContainsString('NOTIFICATION_RECEIVED', $this->navbar);
        $this->assertStringContainsString('navigator.serviceWorker', $this->navbar);
    }

    public function testScriptUsesBaseUrl(): void
    {
        $this->assertStringContainsString("base_url('notificaciones/contador')", $this->navbar);
    }

    public function testServiceWorkerPostsNotificationReceived(): void
    {
        $sw = file_get_contents(FCPATH . 'service-worker.js');
        $this->assertStringContainsString('NOTIFICATION_RECEIVED', $sw);
        $this->assertStringContainsString('postMessage', $sw);
        $this->assertStringContainsString('clients.matchAll', $sw);
    }

    public function testBellVisibleOnAllScreens(): void
    {
        $this->assertStringContainsString('mobile-topbar-notif-bell', $this->navbar);
        $this->assertStringNotContainsString('d-lg-none mobile-topbar-notif-bell', $this->navbar);
    }

    public function testServiceWorkerReturnsMatchAllPromise(): void
    {
        $sw = file_get_contents(FCPATH . 'service-worker.js');
        $this->assertMatchesRegularExpression(
            '/\.then\(\(\) => \{[\s\S]*?return clients\.matchAll/',
            $sw,
            'Service worker must return clients.matchAll() promise inside then callback'
        );
    }

    public function testTopbarLinkHasId(): void
    {
        $this->assertStringContainsString('id="topbar-notif-link"', $this->navbar);
    }

    public function testScriptUpdatesLinkAriaLabel(): void
    {
        $this->assertStringContainsString("getElementById('topbar-notif-link')", $this->navbar);
        $this->assertStringContainsString('ninguna sin leer', $this->navbar);
        $this->assertStringContainsString('sin leer', $this->navbar);
    }

    public function testTopbarBadgeIsDecorative(): void
    {
        $this->assertMatchesRegularExpression(
            '/id="topbar-notif-badge"[^>]*aria-hidden="true"/',
            $this->navbar,
            'Topbar badge must have aria-hidden="true" for decorative purposes'
        );
    }

    public function testMultipleActionsClassConditional(): void
    {
        $this->assertStringContainsString('mobile-topbar-actions--multiple', $this->navbar);
        $this->assertMatchesRegularExpression(
            '/mobileTopbarActions.*mobile-topbar-actions--multiple/s',
            $this->navbar
        );
    }

    public function testNavbarRenderWithMobileTopbarActions(): void
    {
        $navbarContent = file_get_contents(APPPATH . 'Views/partials/_navbar.php');

        $this->assertStringContainsString('mobile-topbar-actions--multiple', $navbarContent);
        $this->assertStringContainsString('$mobileTopbarActions', $navbarContent);
        $this->assertMatchesRegularExpression(
            '/mobile-topbar-actions.*mobile-topbar-actions--multiple.*mobileTopbarActions/s',
            $navbarContent
        );
    }

    public function testNavbarRenderWithoutMobileTopbarActions(): void
    {
        $navbarContent = file_get_contents(APPPATH . 'Views/partials/_navbar.php');

        $this->assertStringContainsString('id="topbar-notif-link"', $navbarContent);
        $this->assertStringContainsString('mobile-topbar-notif-bell', $navbarContent);
    }
}
