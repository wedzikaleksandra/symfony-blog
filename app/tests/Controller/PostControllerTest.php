<?php
/**
 * Post controller tests.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class PostControllerTest.
 */
class PostControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/post';

    /**
     * Test client.
     */
    private KernelBrowser $httpClient;

    /**
     * Set up tests.
     */
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
    }

    /**
     * Test index route for anonymous user.
     */
    public function testIndexRouteAnonymousUser(): void
    {
        // given
        $expectedStatusCode = 200;

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test index route for admin user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testIndexRouteAdminUser(): void
    {
        // given
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value,], 'test_category__admin@example.com');
        $this->httpClient->loginUser($adminUser);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test show single post.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testShowSinglePost(): void
    {
        // given
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value,], 'test_show_post@example.com');
        $this->httpClient->loginUser($adminUser);

        $expectedPost = new Post();
        $expectedPost->setTitle('Test Post 1');
        $expectedPost->setContent('TestPostCreated');
        $expectedPost->setCreatedAt(new \DateTimeImmutable('now'));
        $expectedPost->setUpdatedAt(new \DateTimeImmutable('now'));
        $expectedPost->setSlug('test-post-1');
        $expectedPost->setCategory($this->createCategory('Test Category 15'));
        $postRepository = static::getContainer()->get(PostRepository::class);
        $postRepository->save($expectedPost);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $expectedPost->getId());
        $result = $this->httpClient->getResponse();

        // then
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertSelectorTextContains('html', $expectedPost->getTitle());
    }

    /**
     * Test create post.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testCreatePost(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_ADMIN->value], 'test_post_create@example.com');
        $this->httpClient->loginUser($user);
        $category = $this->createCategory('Test Category 14');
        $this->httpClient->request('GET', self::TEST_ROUTE . '/create');

        // when
        $this->httpClient->submitForm(
            'Utwórz',
            ['post' =>
                [
                    'title' => 'Test Post',
                    'content' => 'TestPostCreated',
                    'category' => $category->getId(),
                ]
            ]
        );

        // then
        $this->assertEquals(302, $this->httpClient->getResponse()->getStatusCode());
    }


    /**
     * Test edit post.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testEditPost(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_ADMIN->value], 'test_post_edit@example.com');
        $this->httpClient->loginUser($user);

        $postRepository = static::getContainer()->get(PostRepository::class);
        $testPost = new Post();
        $testPost->setTitle('edited post');
        $testPost->setCreatedAt(new \DateTimeImmutable('now'));
        $testPost->setUpdatedAt(new \DateTimeImmutable('now'));
        $testPost->setSlug('edited-post');
        $testPost->setCategory($this->createCategory('Test Category 13'));
        $testPost->setContent('TestPostCreated');
        $postRepository->save($testPost);
        $testPostId = $testPost->getId();
        $expectedNewPostTitle = 'test post edit';

        $this->httpClient->request(
            'GET', self::TEST_ROUTE . '/' .
            $testPostId . '/edit'
        );

        // when
        $this->httpClient->submitForm(
            'Edytuj',
            ['post' => ['title' => $expectedNewPostTitle]]
        );

        // then
        $savedPost = $postRepository->findOneById($testPostId);
        $this->assertEquals($expectedNewPostTitle, $savedPost->getTitle());
    }

    /**
     * Test delete post.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testDeletePost(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_ADMIN->value], 'test_post_delete@example.com');
        $this->httpClient->loginUser($user);

        $postRepository = static::getContainer()->get(PostRepository::class);
        $testPost = new Post();
        $testPost->setTitle('TestPostCreated');
        $testPost->setCreatedAt(new \DateTimeImmutable('now'));
        $testPost->setUpdatedAt(new \DateTimeImmutable('now'));
        $testPost->setContent('TestPostCreated');
        $testPost->setCategory($this->createCategory('Test Category 12'));
        $postRepository->save($testPost);
        $testPostId = $testPost->getId();

        $this->httpClient->request('GET', self::TEST_ROUTE . '/' . $testPostId . '/delete');

        // when
        $this->httpClient->submitForm(
            'Usuń'
        );

        // then
        $this->assertNull($postRepository->findOneByTitle('TestPostCreated'));
    }


    /**
     * Create user.
     *
     * @param array $roles User roles
     *
     * @return User User entity
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    private function createUser(array $roles, $email): User
    {
        $passwordHasher = static::getContainer()->get('security.password_hasher');
        $user = new User();
        $user->setEmail($email);
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setRoles($roles);
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                'p@55w0rd'
            )
        );
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($user, true);

        return $user;
    }

    /**
     * Create Category.
     *
     * @param string $name Category name
     *
     * @return Category Category entity
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    private function createCategory(string $name): Category
    {
        $category = new Category();
        $category->setTitle($name);
        $category->setCreatedAt(new \DateTimeImmutable('now'));
        $category->setUpdatedAt(new \DateTimeImmutable('now'));
        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($category);

        return $category;
    }
}