<!DOCTYPE html>
<html>
<head>
    <title>Лента постов</title>
</head>
<body>
<h1>Лента постов</h1>

<div style="display: flex;">

    <div style="width: 200px; padding-right: 20px;">
        <h2>Фильтры</h2>

        <form action="/" method="GET">
            <label for="author_nickname">Фильтр по никнейму автора:</label><br>
            <input type="text" id="author_nickname" name="author_nickname" value="<?php echo isset($_GET['author_nickname']) ? htmlspecialchars($_GET['author_nickname']) : ''; ?>"><br><br>

            <label for="sort_by">Сортировать по:</label><br>
            <select name="sort_by" id="sort_by">
                <option value="">Не выбрано</option>
                <option value="author" <?php echo isset($_GET['sort_by']) && $_GET['sort_by'] === 'author' ? 'selected' : ''; ?>>Автору</option>
                <option value="likes" <?php echo isset($_GET['sort_by']) && $_GET['sort_by'] === 'likes' ? 'selected' : ''; ?>>Лайкам</option>
                <option value="comments" <?php echo isset($_GET['sort_by']) && $_GET['sort_by'] === 'comments' ? 'selected' : ''; ?>>Комментариям</option>
            </select><br><br>

            <label for="order">Порядок:</label><br>
            <select name="order" id="order">
                <option value="asc" <?php echo isset($_GET['order']) && $_GET['order'] === 'asc' ? 'selected' : ''; ?>>Возрастанию</option>
                <option value="desc" <?php echo isset($_GET['order']) && $_GET['order'] === 'desc' ? 'selected' : ''; ?>>Убыванию</option>
            </select><br><br>

            <button type="submit">Применить</button>
            <a href="/">Сбросить фильтры</a>
        </form>
    </div>

    <div>
        <?php
        // Временная проверка роли пользователя (заглушка)
        $userRole = 'moder'; // Здесь должна быть реальная логика получения роли
        if ($userRole === 'writer' || $userRole === 'moder' || $userRole === 'admin'): ?>
            <a href="/post/create">Написать пост</a>
        <?php endif; ?>

        <?php foreach ($posts as $post): ?>
            <div style="margin-bottom: 20px; border: 1px solid #ccc; padding: 10px;">
                <h3><?php echo htmlspecialchars($post->title); ?></h3>
                <p><?php echo htmlspecialchars($post->content); ?></p>
                <p>Автор: <?php echo htmlspecialchars($authorName[$post->id]); ?></p>
                <p>Лайков: <?php echo htmlspecialchars($post->likes); ?></p>
                <p>Комментариев: <?php echo htmlspecialchars($post->commentCount); ?></p>
                <form action="/post/<?php echo htmlspecialchars($post->id); ?>/like" method="post">
                    <button type="submit">Лайк</button>
                </form>
                <a href="/post/<?php echo htmlspecialchars($post->id); ?>">Комментарии</a>

                <?php
                // Временная проверка роли для редактирования (заглушка)
                if ($userRole === 'moder' || $userRole === 'admin'): ?>
                    <a href="/post/edit/<?php echo htmlspecialchars($post->id); ?>">Редактировать</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

</div>

</body>
</html>