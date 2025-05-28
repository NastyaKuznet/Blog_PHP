SELECT p.*, 
       u.login as user_login, 
       u2.login as last_editor_login,
       ca.id as category_id,
       ca.name as category_name,
       COALESCE(l.like_count, 0) as like_count,
       COALESCE(c.comment_count, 0) as comment_count
FROM posts p
JOIN users u ON p.author_id = u.id
JOIN users u2 ON p.last_editor_id = u2.id
LEFT JOIN (
    SELECT post_id, COUNT(*) as like_count
    FROM likes
    GROUP BY post_id
) l ON p.id = l.post_id
LEFT JOIN (
    SELECT post_id, COUNT(*) as comment_count
    FROM comments
    WHERE is_delete = false
    GROUP BY post_id
) c ON p.id = c.post_id
LEFT JOIN category_posts cp ON cp.post_id = p.id
LEFT JOIN categories ca ON ca.id = cp.category_id
WHERE p.is_publish = true AND p.is_delete = false;
