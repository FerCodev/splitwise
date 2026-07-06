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

    public function testMobileMenuBadgeKeepsItsOwnGridColumn(): void
    {
        $css = file_get_contents(FCPATH . 'assets/app.css');

        $this->assertStringContainsString('mobile-menu-link--with-badge', $this->navbar);
        $this->assertStringContainsString('grid-template-columns: 22px minmax(0, 1fr) auto', $css);
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

    public function testUnreadCountUpdatesInstalledAppBadge(): void
    {
        $this->assertStringContainsString('navigator.setAppBadge(count)', $this->navbar);
        $this->assertStringContainsString('navigator.clearAppBadge()', $this->navbar);
    }

    public function testPushSetsBackgroundAppBadge(): void
    {
        $sw = file_get_contents(FCPATH . 'service-worker.js');
        $this->assertStringContainsString('self.navigator.setAppBadge(count)', $sw);
        $this->assertStringContainsString('setAppBadge(1)', $sw);
    }

    public function testServiceWorkerUsesDedicatedMonochromeNotificationBadge(): void
    {
        $sw = file_get_contents(FCPATH . 'service-worker.js');

        $this->assertStringContainsString('notification-badge.png', $sw);
        $this->assertFileExists(FCPATH . 'assets/pwa/notification-badge.png');
        $this->assertSame([96, 96], array_slice(getimagesize(FCPATH . 'assets/pwa/notification-badge.png'), 0, 2));
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

    public function testMultipleActionsClassOnContainer(): void
    {
        $navbarContent = file_get_contents(APPPATH . 'Views/partials/_navbar.php');

        $this->assertStringContainsString('mobile-topbar--multiple-actions', $navbarContent);
        $this->assertMatchesRegularExpression(
            '/mobile-topbar.*mobile-topbar--multiple-actions.*mobileTopbarActions/s',
            $navbarContent,
            'Class mobile-topbar--multiple-actions must be on container, conditional on mobileTopbarActions'
        );
    }

    public function testCSSUsesDescendantSelectorNotSibling(): void
    {
        $css = file_get_contents(FCPATH . 'assets/app.css');

        $this->assertStringContainsString('.mobile-topbar--multiple-actions .mobile-page-title', $css);
        $this->assertStringNotContainsString('.mobile-topbar-actions--multiple ~ .mobile-page-title', $css);
    }

    public function testCSSTitleHasSymmetricSpacing(): void
    {
        $css = file_get_contents(FCPATH . 'assets/app.css');

        $this->assertMatchesRegularExpression(
            '/\.mobile-topbar--multiple-actions \.mobile-page-title \{[^}]*left:\s*(\d+)px;[^}]*right:\s*\1px;/s',
            $css,
            'Title must have equal left and right values for symmetric centering'
        );
    }

    public function testHomeWithoutActionsHasNoMultipleClass(): void
    {
        $navbarContent = file_get_contents(APPPATH . 'Views/partials/_navbar.php');

        $this->assertMatchesRegularExpression(
            '/mobile-topbar.*mobileTopbarActions.*mobile-topbar--multiple-actions/s',
            $navbarContent,
            'Class must be conditional on mobileTopbarActions variable'
        );
    }

    public function testReportesWithActionsHasMultipleClass(): void
    {
        $navbarContent = file_get_contents(APPPATH . 'Views/partials/_navbar.php');

        $this->assertStringContainsString('mobile-topbar--multiple-actions', $navbarContent);
        $this->assertMatchesRegularExpression(
            '/container mobile-topbar.*mobileTopbarActions/s',
            $navbarContent
        );
    }
}
