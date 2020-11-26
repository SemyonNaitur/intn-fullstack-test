<?php
$top_nav = [
    'create-post' => [
        'text' => 'Create Post',
        'name' => 'create-post',
        'active' => '',
    ],
    'posts' => [
        'text' => 'Posts',
        'name' => 'posts',
        'active' => '',
    ],
    'stats' => [
        'text' => 'Statistics',
        'name' => 'stats',
        'active' => '',
    ],
];

if (!empty($page)) {
    if (isset($top_nav[$page])) {
        $top_nav[$page]['active'] = 'active';
    }
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#topNav" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="topNav">
        <ul class="navbar-nav mr-auto">
            <?php foreach ($top_nav as $data) { ?>
                <li class="nav-item <?= $data['active'] ?>">
                    <a class="nav-link" href="assignments/intn-blog/<?= $data['name'] ?>"><?= $data['text'] ?></a>
                </li>
            <?php } ?>
        </ul>
    </div>
</nav>