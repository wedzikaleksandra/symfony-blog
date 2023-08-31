<?php
/**
 * Category service.
 */

namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class CategoryService.
 */
class CategoryService implements CategoryServiceInterface
{
    /**
     * Category repository.
     */
    private CategoryRepository $categoryRepository;

    /**
     * Post repository.
     */
    private PostRepository $postRepository;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * CategoryService constructor.
     *
     * @param CategoryRepository $categoryRepository Category repository
     * @param PaginatorInterface $paginator          Paginator
     * @param PostRepository     $postRepository     Post repository
     */
    public function __construct(CategoryRepository $categoryRepository, PaginatorInterface $paginator, PostRepository $postRepository)
    {
        $this->categoryRepository = $categoryRepository;
        $this->postRepository = $postRepository;
        $this->paginator = $paginator;
    }

    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->categoryRepository->queryAll(),
            $page,
            CategoryRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Get paginated list by category.
     *
     * @param int      $page     Page number
     * @param Category $category Category entity
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function createPostByCategoryPaginatedList(int $page, Category $category): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->postRepository->queryByCategory($category),
            $page,
            PostRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save entity.
     *
     * @param Category $category Category entity
     */
    public function save(Category $category): void
    {
        $this->categoryRepository->save($category);
    }

    /**
     * Delete entity.
     *
     * @param Category $category Category entity
     */
    public function delete(Category $category): void
    {
        $this->categoryRepository->delete($category);
    }

    /**
     * Can Category be deleted?
     *
     * @param Category $category Category entity
     *
     * @return bool Result
     *
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function canBeDeleted(Category $category): bool
    {
        $result = $this->postRepository->countByCategory($category);

        return !($result > 0);
    }

    /**
     * Find one by id.
     *
     * @param int $id Category id
     *
     * @return Category|null Category entity
     *
     * @throws NonUniqueResultException
     */
    public function findOneById(int $id): ?Category
    {
        return $this->categoryRepository->findOneById($id);
    }
}
