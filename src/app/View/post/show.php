<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($post['title']); ?></title>
</head>
<body>
<h1><?php echo htmlspecialchars($post['title']); ?></h1>

<p><?php echo htmlspecialchars($post['content']); ?></p>
<p>Автор: <?php echo htmlspecialchars($authorName); ?></p>

<h2>Комментарии:</h2>
<?php if (!empty($comments)): ?>
    <?php foreach ($comments as $comment): ?>
        <div style="margin-bottom: 10px; border: 1px solid #eee; padding: 5px;">
            <p><b><?php echo htmlspecialchars($comment['author']); ?>:</b> <?php echo htmlspecialchars($comment['content']); ?></p>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>Нет комментариев.</p>
<?php endif; ?>

<h2>Добавить комментарий:</h2>
<form action="/post/<?php echo htmlspecialchars($post['id']); ?>" method="POST">
    <label for="comment">Комментарий:</label><br>
    <textarea id="comment" name="comment" rows="4" cols="50" required></textarea><br>
    <button type="submit">Отправить комментарий</button>
    <a href="/">Лента</a>
</form>

</body>
</html>