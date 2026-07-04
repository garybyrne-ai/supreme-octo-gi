<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Models\ForumRepository;
use App\Services\AuditLogger;

final class ForumController extends Controller
{
    public function index(): void
    {
        Security::ensureSession();
        $forum = new ForumRepository();
        $topics = $forum->topics();

        $this->render('forum/index', [
            'title' => 'Tech Forum | Crest Web Media',
            'metaDescription' => 'Crest Web Media tech forum for SEO, backlinks, AI, web development, ecommerce, PPC and website security discussions.',
            'topics' => $topics,
            'users' => $forum->users(),
            'member' => $_SESSION['member'] ?? null,
            'notice' => $_SESSION['forum_notice'] ?? null,
            'error' => $_SESSION['forum_error'] ?? null,
        ]);
        unset($_SESSION['forum_notice'], $_SESSION['forum_error']);
    }

    public function show(string $slug): void
    {
        Security::ensureSession();
        $forum = new ForumRepository();
        $topic = $forum->topic($slug);

        if ($topic === null) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Forum Topic Not Found']);
            return;
        }

        $this->render('forum/show', [
            'title' => $topic['title'] . ' | Crest Tech Forum',
            'metaDescription' => $topic['excerpt'],
            'topic' => $topic,
            'backlinkList' => str_starts_with((string) $topic['title'], 'Top 50 Irish') ? $forum->backlinkList() : [],
            'member' => $_SESSION['member'] ?? null,
            'csrf' => Security::csrfToken(),
            'notice' => $_SESSION['forum_notice'] ?? null,
            'error' => $_SESSION['forum_error'] ?? null,
        ]);
        unset($_SESSION['forum_notice'], $_SESSION['forum_error']);
    }

    public function storePost(string $slug): void
    {
        Security::ensureSession();

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            $_SESSION['forum_error'] = 'Forum token expired. Please try again.';
            $this->redirect('/forum/' . $slug);
        }

        $member = $_SESSION['member'] ?? null;
        if (!is_array($member) || empty($member['email'])) {
            $_SESSION['forum_error'] = 'Please register or login before posting.';
            $this->redirect('/forum/' . $slug);
        }

        if (empty($member['forum_verified'])) {
            $_SESSION['forum_error'] = 'Your account is registered but forum posting needs admin approval first.';
            $this->redirect('/forum/' . $slug);
        }

        try {
            (new ForumRepository())->storePost($slug, $member, (string) ($_POST['body'] ?? ''));
            $_SESSION['forum_notice'] = 'Reply posted.';
            (new AuditLogger())->log('forum.reply.created', ['topic' => $slug, 'email' => $member['email'] ?? '']);
        } catch (\Throwable $exception) {
            $_SESSION['forum_error'] = $exception->getMessage();
        }

        $this->redirect('/forum/' . $slug . '#reply');
    }
}
