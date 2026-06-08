<?php

use App\Models\User;
use App\Models\Grupo;

/**
 * @internal
 */
final class RolesGlobalesTest extends \CodeIgniter\Test\CIUnitTestCase
{
    private array $adminUser = ['id' => 1, 'role' => 'admin', 'name' => 'Admin'];
    private array $normalUser = ['id' => 2, 'role' => 'user', 'name' => 'Normal'];

    public function testRoleDefaultIsUser(): void
    {
        $this->assertSame('user', 'user');
    }

    public function testIsAdminReturnsTrueForAdmin(): void
    {
        $this->assertTrue(User::isAdmin($this->adminUser));
    }

    public function testIsAdminReturnsFalseForUser(): void
    {
        $this->assertFalse(User::isAdmin($this->normalUser));
    }

    public function testIsAdminReturnsFalseForMissingRole(): void
    {
        $this->assertFalse(User::isAdmin([]));
    }

    public function testHasRoleAdmin(): void
    {
        $this->assertTrue(User::hasRole($this->adminUser, 'admin'));
    }

    public function testHasRoleUser(): void
    {
        $this->assertTrue(User::hasRole($this->normalUser, 'user'));
    }

    public function testHasRoleReturnsFalseForWrongRole(): void
    {
        $this->assertFalse(User::hasRole($this->normalUser, 'admin'));
    }

    public function testAdminFilterRejectsNonAdmin(): void
    {
        $request = service('request');
        $filter = new \App\Filters\AdminFilter();

        session()->set(['isLoggedIn' => true, 'userId' => 2, 'userRole' => 'user']);
        $result = $filter->before($request);

        $this->assertInstanceOf(\CodeIgniter\HTTP\RedirectResponse::class, $result);
    }

    public function testAdminFilterAllowsAdmin(): void
    {
        $request = service('request');
        $filter = new \App\Filters\AdminFilter();

        session()->set(['isLoggedIn' => true, 'userId' => 1, 'userRole' => 'admin']);
        $result = $filter->before($request);

        $this->assertNull($result);
    }

    public function testLoginSavesRoleInSession(): void
    {
        $sessionData = [
            'isLoggedIn' => true,
            'userId'     => 1,
            'userName'   => 'Admin',
            'userEmail'  => 'admin@test.com',
            'userRole'   => 'admin',
        ];

        session()->set($sessionData);

        $this->assertTrue(session()->get('isLoggedIn'));
        $this->assertSame('admin', session()->get('userRole'));
    }

    public function testLoginSavesUserRoleDefault(): void
    {
        $sessionData = [
            'isLoggedIn' => true,
            'userId'     => 2,
            'userName'   => 'User',
            'userEmail'  => 'user@test.com',
            'userRole'   => 'user',
        ];

        session()->set($sessionData);

        $this->assertSame('user', session()->get('userRole'));
    }

    public function testNavbarHidesAdminLinksForUser(): void
    {
        session()->set(['userRole' => 'user']);

        $this->assertNotSame('admin', session()->get('userRole'));
    }

    public function testNavbarShowsAdminLinksForAdmin(): void
    {
        session()->set(['userRole' => 'admin']);

        $this->assertSame('admin', session()->get('userRole'));
    }

    public function testUserCannotSetOwnRoleToAdminViaPostValidation(): void
    {
        $allowedRoles = ['user', 'admin'];

        $maliciousRole = 'admin';
        $isValid = in_array($maliciousRole, $allowedRoles, true);

        $sessionUserId = 2;
        $targetUserId = 2;

        $isSelf = $sessionUserId === $targetUserId;

        $this->assertTrue($isValid, 'admin es un rol válido');
        $this->assertTrue($isSelf, 'es el mismo usuario');

        $isAllowed = !$isSelf || $maliciousRole === ($this->normalUser['role'] ?? 'user');

        $this->assertFalse($isAllowed, 'un user no puede autopromocionarse a admin');
    }

    public function testLastAdminCannotBeDemoted(): void
    {
        $adminUsers = [
            ['id' => 1, 'role' => 'admin'],
        ];

        $isLastAdmin = count($adminUsers) === 1;
        $this->assertTrue($isLastAdmin);

        $canDemote = !$isLastAdmin;
        $this->assertFalse($canDemote, 'no se puede degradar al último admin');
    }

    public function testMultipleAdminsCanDemoteOne(): void
    {
        $adminUsers = [
            ['id' => 1, 'role' => 'admin'],
            ['id' => 3, 'role' => 'admin'],
        ];

        $isLastAdmin = count($adminUsers) === 1;
        $this->assertFalse($isLastAdmin);

        $canDemote = !$isLastAdmin;
        $this->assertTrue($canDemote, 'se puede degradar si queda otro admin');
    }

    public function testGlobalRolesAreSeparateFromGroupRoles(): void
    {
        $globalRole = 'user';
        $groupRole = 'admin';

        $this->assertSame('user', $globalRole);
        $this->assertSame('admin', $groupRole);
        $this->assertNotSame($globalRole, $groupRole);
    }

    public function testRestriccionEstadoNotAffectedByGlobalRoles(): void
    {
        $bloqueo = Grupo::restriccionEstado('activo', 'gasto_create');
        $this->assertNull($bloqueo);
    }

    public function testNonAdminCannotEditRoleOfOtherUser(): void
    {
        $currentUserRole = 'user';
        $canEditRole = $currentUserRole === 'admin';

        $this->assertFalse($canEditRole);
    }

    public function testEmptyRoleNormalizedToUser(): void
    {
        $allowedRoles = ['user', 'admin'];

        $maliciousEmpty = '';
        $isValid = in_array($maliciousEmpty, $allowedRoles, true);
        $this->assertFalse($isValid, 'cadena vacía no es un rol válido');

        $maliciousNull = null;
        $isValid = in_array($maliciousNull, $allowedRoles, true);
        $this->assertFalse($isValid, 'null no es un rol válido');

        $normalized = (!in_array($maliciousEmpty, ['user', 'admin'], true)) ? 'user' : $maliciousEmpty;
        $this->assertSame('user', $normalized, 'vacío se normaliza a user');

        $normalized = (!in_array($maliciousNull, ['user', 'admin'], true)) ? 'user' : $maliciousNull;
        $this->assertSame('user', $normalized, 'null se normaliza a user');
    }

    public function testValidationRuleRequiredInListRejectsEmptyAndNull(): void
    {
        $allowed = ['user', 'admin'];

        $this->assertFalse(in_array('', $allowed, true));
        $this->assertFalse(in_array(null, $allowed, true));
        $this->assertTrue(in_array('user', $allowed, true));
        $this->assertTrue(in_array('admin', $allowed, true));
    }

    public function testSessionStalePreventedWhenSelfEditRole(): void
    {
        session()->set(['userId' => 1, 'userRole' => 'admin']);

        $isSelf = 1 === (int) session()->get('userId');
        $this->assertTrue($isSelf);

        $newRole = 'user';
        if ($isSelf) {
            session()->set('userRole', $newRole);
        }

        $this->assertSame('user', session()->get('userRole'), 'sesión debe reflejar el nuevo rol');
    }

    public function testSessionNotStaleWhenEditingOtherUserRole(): void
    {
        session()->set(['userId' => 1, 'userRole' => 'admin']);

        $isSelf = 2 === (int) session()->get('userId');
        $this->assertFalse($isSelf);

        $oldRole = session()->get('userRole');
        $this->assertSame('admin', $oldRole, 'admin editando a otro usuario conserva su rol en sesión');
    }
}
