<?php
/**
 * Comment service interface.
 */

namespace App\Service;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Interface CommentServiceInterface.
 */
class CommentService implements CommentServiceInterface
{
    /**
     * Comment repository.
     */
    private CommentRepository $commentRepository;

    /**
     * Post repository.
     */
    private PostRepository $postRepository;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * CommentService constructor.
     *
     * @param CommentRepository  $commentRepository Comment repository
     * @param PaginatorInterface $paginator         Paginator
     * @param PostRepository     $postRepository    Post repository
     */
    public function __construct(CommentRepository $commentRepository, PaginatorInterface $paginator, PostRepository $postRepository)
    {
        $this->commentRepository = $commentRepository;
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
            $this->commentRepository->queryAll(),
            $page,
            CommentRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save entity.
     *
     * @param Comment $comment Comment entity
     * @param int     $postId  Post id
     */
    public function save(Comment $comment, int $postId): void
    {
        $post = $this->postRepository->findOneBy(['id' => $postId]);
        $comment->setPost($post);
        $this->commentRepository->save($comment);
    }

    /**
     * Delete entity.
     *
     * @param Comment $comment Comment entity
     */
    public function delete(Comment $comment): void
    {
        $this->commentRepository->delete($comment);
    }

    /**
     * Update entity.
     *
     * @param Comment $comment Comment entity
     */
    public function update(Comment $comment): void
    {
        $this->commentRepository->save($comment);
    }
}
