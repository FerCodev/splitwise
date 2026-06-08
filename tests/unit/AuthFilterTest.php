<?php

use CodeIgniter\Test\CIUnitTestCase;
use App\Filters\AuthFilter;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
final class AuthFilterTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
    }

    public function testBeforeWithoutSessionRedirectsToLogin(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $filter = new AuthFilter();

        $result = $filter->before($request);

        $this->assertInstanceOf(\CodeIgniter\HTTP\RedirectResponse::class, $result);
    }

    public function testAuthFilterDoesNotBlockWithActiveSession(): void
    {
        $_SESSION['isLoggedIn'] = true;

        $request = $this->createMock(RequestInterface::class);
        $filter = new AuthFilter();

        $result = $filter->before($request);

        $this->assertNull($result);
    }
}