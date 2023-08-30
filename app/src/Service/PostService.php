<?php
/**
 * Post service.
 */

namespace App\Service;

use App\Entity\Post;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class PostService.
 */
class PostService implements PostServiceInterface
{
    /**
     * Post repository.
     */
    private PostRepository $postRepository;

    /**
     * Comment repository.
     */
    private CommentRepository $commentRepository;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * CategoryService constructor.
     *
     * @param PostRepository     $postRepository Post repository
     * @param CommentRepository $commentRepository Comment repository
     * @param PaginatorInterface $paginator      Paginator
     */
    public function __construct(PostRepository $postRepository, PaginatorInterface $paginator, CommentRepository $commentRepository)
    {
        $this->postRepository = $postRepository;
        $this->commentRepository = $commentRepository;
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
            $this->postRepository->queryAll(),
            $page,
            PostRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * @param int $page
     * @param Post $post
     * @return PaginationInterface
     */
    public function createCommentByPostPaginatedList(int $page, Post $post): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->commentRepository->queryByPost($post),
            $page,
            CommentRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save entity.
     *
     * @param Post $post Post entity
     */
    public function save(Post $post): void
    {
        $this->postRepository->save($post);
    }

    /**
     * Delete entity.
     *
     * @param Post $post Post entity
     */
    public function delete(Post $post): void
    {
        $comments = $this->commentRepository->findBy(['post' => $post]);
        foreach ($comments as $comment) {
            $this->commentRepository->delete($comment);
        }
        $this->postRepository->delete($post);
    }
}