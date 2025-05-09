-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： localhost
-- 產生時間： 2025 年 05 月 02 日 14:49
-- 伺服器版本： 10.4.28-MariaDB
-- PHP 版本： 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `user_system`
--

-- --------------------------------------------------------

--
-- 資料表結構 `loginattempts`
--

CREATE TABLE `loginattempts` (
  `id` int(11) NOT NULL,
  `user` bigint(20) UNSIGNED NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `posts`
--

CREATE TABLE `posts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT '作者ID',
  `user_name` varchar(255) NOT NULL COMMENT '作者名稱',
  `content` text NOT NULL COMMENT '貼文內容',
  `tag` text NOT NULL COMMENT '貼文標記',
  `image_url` varchar(255) DEFAULT NULL COMMENT '圖片URL',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '狀態(1:正常,0:刪除)',
  `likes_count` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '按讚數',
  `comments_count` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '留言數',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `user_name`, `content`, `tag`, `image_url`, `status`, `likes_count`, `comments_count`, `created_at`, `updated_at`) VALUES
(1, 3, 'leon', '留學', '日本', 'https://leonsproject0.s3.amazonaws.com/posts/6803144590e7b_1745032261.jpeg', 1, 2, 2, '2025-04-19 03:11:33', '2025-04-26 08:07:36'),
(2, 4, 'Ben', '聖誕夜', '倫敦/rich', 'https://leonsproject0.s3.amazonaws.com/posts/680366603b59a_1745053280.jpeg', 1, 1, 1, '2025-04-19 09:01:30', '2025-04-24 03:48:06');

-- --------------------------------------------------------

--
-- 資料表結構 `post_comments`
--

CREATE TABLE `post_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT '留言的用戶',
  `content` text NOT NULL COMMENT '留言內容',
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '父留言ID，用於回覆功能',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '狀態(1:正常,0:刪除)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reply_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `post_comments`
--

INSERT INTO `post_comments` (`id`, `post_id`, `user_id`, `content`, `parent_id`, `status`, `created_at`, `updated_at`, `reply_count`) VALUES
(1, 1, 3, '😍', NULL, 1, '2025-04-22 11:53:21', '2025-04-26 07:23:08', 2),
(3, 1, 3, '好看！！！', 1, 1, '2025-04-24 03:04:59', '2025-04-24 03:04:59', 0),
(4, 2, 4, '聖誕氛圍，我會在天使燈亮起時回到這裡', NULL, 1, '2025-04-24 03:40:40', '2025-04-24 03:40:40', 0),
(5, 1, 4, '是嗎', 1, 1, '2025-04-26 07:23:08', '2025-04-26 07:23:08', 0),
(6, 1, 4, '🙄', NULL, 1, '2025-04-26 08:07:36', '2025-04-26 08:07:36', 0);

--
-- 觸發器 `post_comments`
--
DELIMITER $$
CREATE TRIGGER `after_comment_insert` AFTER INSERT ON `post_comments` FOR EACH ROW BEGIN
    IF NEW.parent_id IS NULL THEN
        UPDATE posts 
        SET comments_count = comments_count + 1 
        WHERE id = NEW.post_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_comment_status_update` AFTER UPDATE ON `post_comments` FOR EACH ROW BEGIN
    IF NEW.status = 0 AND OLD.status = 1 AND NEW.parent_id IS NULL THEN
        UPDATE posts 
        SET comments_count = comments_count - 1 
        WHERE id = NEW.post_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 資料表結構 `post_likes`
--

CREATE TABLE `post_likes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT '按讚的用戶',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `post_likes`
--

INSERT INTO `post_likes` (`id`, `post_id`, `user_id`, `created_at`) VALUES
(5, 2, 3, '2025-04-21 13:38:48'),
(11, 1, 3, '2025-04-23 11:54:45'),
(63, 1, 4, '2025-04-26 07:48:09');

--
-- 觸發器 `post_likes`
--
DELIMITER $$
CREATE TRIGGER `after_like_delete` AFTER DELETE ON `post_likes` FOR EACH ROW BEGIN
    UPDATE posts 
    SET likes_count = likes_count - 1 
    WHERE id = OLD.post_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_like_insert` AFTER INSERT ON `post_likes` FOR EACH ROW BEGIN
    UPDATE posts 
    SET likes_count = likes_count + 1 
    WHERE id = NEW.post_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 資料表結構 `requests`
--

CREATE TABLE `requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user` bigint(20) UNSIGNED DEFAULT NULL,
  `hash` varchar(255) DEFAULT NULL,
  `timestamp` int(10) UNSIGNED DEFAULT NULL,
  `type` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `avatar_url` varchar(255) DEFAULT NULL COMMENT '頭像URL',
  `post_amount` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `subscriber_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `follower_count` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `verified`, `avatar_url`, `post_amount`, `subscriber_count`, `follower_count`) VALUES
(3, 'leon', 'zhaoyangr@gmail.com', '$2y$10$imaWX39TvSw20yRZ1VpOCuTL1Sl5GqzNBgFok5XkehWruJMXTKUc.', 1, 'null', 1, 1, 1),
(4, 'Ben', 'abcdefg@gmail.com', '$2y$10$imaWX39TvSw20yRZ1VpOCuTL1Sl5GqzNBgFok5XkehWruJMXTKUc.', 1, 'null', 1, 1, 1),
(5, 'kitty', 'awifhfoiwj@gmail.com', '$2y$10$imaWX39TvSw20yRZ1VpOCuTL1Sl5GqzNBgFok5XkehWruJMXTKUc.', 1, 'null', 0, 0, 0);

-- --------------------------------------------------------

--
-- 資料表結構 `user_followers`
--

CREATE TABLE `user_followers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `follower_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `user_followers`
--

INSERT INTO `user_followers` (`id`, `user_id`, `follower_id`) VALUES
(1, 3, 4),
(4, 4, 3);

-- --------------------------------------------------------

--
-- 資料表結構 `user_subscribers`
--

CREATE TABLE `user_subscribers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `subscriber_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `user_subscribers`
--

INSERT INTO `user_subscribers` (`id`, `user_id`, `subscriber_id`) VALUES
(11, 3, 4),
(7, 4, 3);

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `loginattempts`
--
ALTER TABLE `loginattempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_timestamp` (`user`,`timestamp`),
  ADD KEY `idx_ip_timestamp` (`ip_address`,`timestamp`);

--
-- 資料表索引 `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_posts_idx` (`user_id`,`created_at`);

--
-- 資料表索引 `post_comments`
--
ALTER TABLE `post_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_comments_idx` (`post_id`,`created_at`),
  ADD KEY `user_comments_idx` (`user_id`,`created_at`),
  ADD KEY `parent_id` (`parent_id`);

--
-- 資料表索引 `post_likes`
--
ALTER TABLE `post_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `post_user_like` (`post_id`,`user_id`) COMMENT '防止重複按讚',
  ADD KEY `user_likes_idx` (`user_id`,`created_at`);

--
-- 資料表索引 `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- 資料表索引 `user_followers`
--
ALTER TABLE `user_followers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_follower_unique` (`user_id`,`follower_id`),
  ADD KEY `follower_id` (`follower_id`);

--
-- 資料表索引 `user_subscribers`
--
ALTER TABLE `user_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_subscriber_unique` (`user_id`,`subscriber_id`),
  ADD KEY `subscriber_id` (`subscriber_id`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `loginattempts`
--
ALTER TABLE `loginattempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `posts`
--
ALTER TABLE `posts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `post_comments`
--
ALTER TABLE `post_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `post_likes`
--
ALTER TABLE `post_likes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `requests`
--
ALTER TABLE `requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `user_followers`
--
ALTER TABLE `user_followers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `user_subscribers`
--
ALTER TABLE `user_subscribers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `loginattempts`
--
ALTER TABLE `loginattempts`
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- 資料表的限制式 `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- 資料表的限制式 `post_comments`
--
ALTER TABLE `post_comments`
  ADD CONSTRAINT `post_comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`),
  ADD CONSTRAINT `post_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `post_comments_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `post_comments` (`id`);

--
-- 資料表的限制式 `post_likes`
--
ALTER TABLE `post_likes`
  ADD CONSTRAINT `post_likes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`),
  ADD CONSTRAINT `post_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- 資料表的限制式 `user_followers`
--
ALTER TABLE `user_followers`
  ADD CONSTRAINT `user_followers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_followers_ibfk_2` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`);

--
-- 資料表的限制式 `user_subscribers`
--
ALTER TABLE `user_subscribers`
  ADD CONSTRAINT `user_subscribers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_subscribers_ibfk_2` FOREIGN KEY (`subscriber_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
