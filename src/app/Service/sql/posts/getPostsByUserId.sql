SELECT p.*, 
       u.login as user_login,
       u2.login as last_editor_login, 
       ca.id as category_id,
       ca.name as category_name,
       COUNT(l.id) as like_count,
       COUNT(CASE WHEN c.is_delete = false THEN c.id ELSE NULL END) as comment_count
FROM posts p 
LEFT JOIN comments c ON p.id = c.post_id
LEFT JOIN likes l ON p.id = l.post_id
LEFT JOIN category_posts cp ON cp.post_id = p.id
LEFT JOIN categories ca ON ca.id = cp.category_id
JOIN users u ON p.author_id = u.id  
JOIN users u2 ON p.last_editor_id = u2.id
WHERE u.id = :user_id and p.is_publish = true
GROUP BY p.id, u.login, u2.login, ca.id;