INSERT INTO comments (content, post_id, user_id, created_date, is_edit, is_delete) 
VALUES (:content, :post_id, :user_id, CURRENT_TIMESTAMP, FALSE, FALSE);