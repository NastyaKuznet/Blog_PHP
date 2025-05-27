SELECT c.*, u.login as user_login
FROM comments c
JOIN users u ON c.user_id = u.id
WHERE c.post_id = :post_id AND c.is_delete = FALSE;