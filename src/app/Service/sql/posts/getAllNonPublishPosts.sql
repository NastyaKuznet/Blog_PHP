SELECT p.*, 
       u.login as user_login, 
       u2.login as last_editor_login
FROM posts p
JOIN users u ON p.author_id = u.id
JOIN users u2 ON p.last_editor_id = u2.id
WHERE p.is_publish = false and p.is_delete = false
GROUP BY p.id, u.login, u2.login;