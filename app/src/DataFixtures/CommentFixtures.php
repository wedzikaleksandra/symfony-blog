<?php
/**
 * Comment fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Post;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class CommentFixtures.
 */
class CommentFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Load.
     */
    public function loadData(): void
    {
        for ($i = 0; $i < 100; ++$i) {
            $comment = new Comment();
            $comment->setContent($this->faker->paragraphs(2, true));
            $comment->setEmail($this->faker->email);
            $comment->setNickname($this->faker->userName);
            $comment->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $comment->setUpdatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );

            /** @var Post $post */
            $post = $this->getRandomReference('posts');
            $comment->setPost($post);

            $this->manager->persist($comment);
        }

        $this->manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] of dependencies
     *
     * @psalm-return array{0: Postixtures::class}
     */
    public function getDependencies(): array
    {
        return [
            PostFixtures::class,
        ];
    }
}
