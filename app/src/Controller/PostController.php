<?php
/**
 * Post controller.
 */

namespace App\Controller;

use App\Entity\Post;
use App\Form\Type\PostType;
use App\Service\PostServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class PostController.
 */
#[Route('/post')]
class PostController extends AbstractController
{
    /**
     * Post service.
     */
    private PostServiceInterface $postService;

    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * PostController constructor.
     *
     * @param PostServiceInterface $postService Post service
     * @param TranslatorInterface  $translator  Translator
     */
    public function __construct(PostServiceInterface $postService, TranslatorInterface $translator)
    {
        $this->postService = $postService;
        $this->translator = $translator;
    }

    /**
     * Index action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'post_index',
        methods: ['GET']
    )]
    public function index(Request $request): Response
    {
        $pagination = $this->postService->getPaginatedList(
            $request->query->getInt('page', 1)
        );

        return $this->render(
            'post/index.html.twig',
            ['pagination' => $pagination]
        );
    }

    /**
     * Show action.
     *
     * @param Post    $post    Post entity
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'post_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET'],
    )]
    public function show(Post $post, Request $request): Response
    {
        $commentByPostPagedList = $this->postService->createCommentByPostPaginatedList(
            $request->query->getInt('page', 1),
            $post
        );

        return $this->render(
            'post/show.html.twig',
            [
                'post' => $post,
                'pagination' => $commentByPostPagedList,
            ]
        );
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/create',
        name: 'post_create',
        methods: 'GET|POST',
    )]
    #[IsGranted('CREATE')]
    public function create(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(
            PostType::class,
            $post,
            [
                'method' => 'POST',
                'action' => $this->generateUrl('post_create'),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->postService->save($post);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('post_index');
        }

        return $this->render(
            'post/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Post    $post    Post entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/edit',
        name: 'post_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET', 'POST'],
    )]
    #[IsGranted('EDIT')]
    public function edit(Request $request, Post $post): Response
    {
        $form = $this->createForm(
            PostType::class,
            $post,
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->postService->save($post);

            $this->addFlash(
                'success',
                $this->translator->trans('message.updated_successfully')
            );

            return $this->redirectToRoute('post_index');
        }

        return $this->render(
            'post/edit.html.twig',
            [
                'form' => $form->createView(),
                'post' => $post,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Post    $post    Post entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/delete',
        name: 'post_delete',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET', 'DELETE'],
    )]
    #[IsGranted('DELETE')]
    public function delete(Request $request, Post $post): Response
    {
        $form = $this->createForm(
            FormType::class,
            $post,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl(
                    'post_delete',
                    ['id' => $post->getId()]
                ),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->postService->delete($post);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('post_index');
        }

        return $this->render(
            'post/delete.html.twig',
            [
                'form' => $form->createView(),
                'post' => $post,
            ]
        );
    }
}
