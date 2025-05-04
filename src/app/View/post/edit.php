<!DOCTYPE html>
<html>
   <head>
       <title>Редактирование поста</title>
   </head>
   <body>
   <h1>Редактирование поста</h1>

   <form action="/post/edit/<?php echo htmlspecialchars($post['id']); ?>" method="POST">
       <div>
           <label for="title">Заголовок:</label><br>
           <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
       </div>
       <div>
           <label for="content">Содержание:</label><br>
           <textarea id="content" name="content" rows="5" cols="50" required><?php echo htmlspecialchars($post['content']); ?></textarea>
       </div>
       <div>
           <button type="submit" name="action" value="save">Сохранить изменения</button>
           <button type="submit" name="action" value="delete">Удалить пост</button>
           <a href="/">Лента</a>
       </div>
   </form>

   </body>
</html>