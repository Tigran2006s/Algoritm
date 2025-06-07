-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: localhost:8889
-- Время создания: Май 27 2025 г., 20:15
-- Версия сервера: 5.7.39
-- Версия PHP: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `algoritm_db_777`
--

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `status` enum('pending','in_progress','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `requirements` text COLLATE utf8mb4_unicode_ci,
  `deadline` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `service_id`, `status`, `total_price`, `created_at`, `updated_at`, `requirements`, `deadline`) VALUES
(3, 2, 15, 'in_progress', '40034.00', '2025-05-23 18:27:49', '2025-05-26 17:13:20', 'о', '2025-05-12'),
(4, 2, 17, 'completed', '2000.00', '2025-05-27 13:58:31', '2025-05-27 14:09:38', 'ТЕСТ 27 МАЯ', '2025-05-27');

-- --------------------------------------------------------

--
-- Структура таблицы `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `review_category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Общий отзыв'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `service_id`, `rating`, `comment`, `status`, `created_at`, `updated_at`, `review_category`) VALUES
(3, 2, 18, 5, 'Кайф', 'approved', '2025-05-08 17:53:54', '2025-05-23 18:37:29', 'Общий отзыв'),
(4, 2, 17, 5, 'Ап-чи', 'approved', '2025-05-08 18:43:32', '2025-05-23 18:37:10', 'Общий отзыв'),
(5, 2, 19, 3, 'м', 'rejected', '2025-05-08 18:44:06', '2025-05-23 17:31:00', 'Общий отзыв'),
(6, 2, NULL, 1, 'И-го-го', 'pending', '2025-05-08 19:10:51', '2025-05-08 19:10:51', 'Налоговое консультирование'),
(7, 2, NULL, 5, 'ТЕСТОВЫЙ ОТЗЫВ НА МОДЕРАЦИЮ', 'rejected', '2025-05-23 18:33:36', '2025-05-25 10:56:26', 'Другое'),
(8, 2, NULL, 5, 'ОТЗАВ СУББОТА МОДЕР ТЕСТ', 'approved', '2025-05-24 11:40:32', '2025-05-25 10:56:15', 'Аудит');

-- --------------------------------------------------------

--
-- Структура таблицы `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brief_description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_days` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `services`
--

INSERT INTO `services` (`id`, `title`, `brief_description`, `full_description`, `price`, `duration_days`, `created_at`, `updated_at`) VALUES
(14, 'Бухгалтерское обслуживание нотариусов с сотрудниками', 'Полный комплекс бухгалтерских и налоговых услуг для нотариальных контор с сотрудниками', 'Компания «Алгоритм» предоставляет полный комплекс бухгалтерских услуг для нотариусов, имеющих сотрудников (1-2 человека).\n\nЧто входит в услугу:\n- Кадровый учет, расчет заработной платы\n- Ведение бухгалтерского и налогового учета\n- Ежегодное ведение налогового учета доходов и расходов (декларация 3-НДФЛ, КУДИР, взаимодействие с ИФНС)\n- Подготовка статистической отчетности при небольшом объеме документов\n- Консультации по вопросам учета и налогообложения', '60000.00', 12, '2025-05-08 16:54:59', '2025-05-08 16:54:59'),
(15, 'Бухгалтерское обслуживание нотариусов без сотрудников', 'Комплексное бухгалтерское и налоговое сопровождение для нотариусов без сотрудников', 'Компания «Алгоритм» оказывает услуги по квартальному и годовому обслуживанию нотариусов, работающих без сотрудников.\r\n\r\nЧто входит в услугу:\r\n- Квартальные консультации, расчет налогов, составление платежных поручений\r\n- Годовое ведение налогового учета (декларация 3-НДФЛ, КУДИР, сверка с ИФНС)\r\n- Подготовка статистической отчетности при небольшом объеме документов\r\n- Консультации по вопросам учета и налогообложения', '40034.00', 14, '2025-05-08 16:54:59', '2025-05-23 17:29:24'),
(16, 'Консультации и составление деклараций без разбора документов', 'Быстрая консультация и подготовка декларации 3-НДФЛ без анализа документов', 'Оперативная консультация и составление налоговой декларации 3-НДФЛ для нотариусов и физических лиц без разбора и анализа предоставленных документов.\n\nЧто входит в услугу:\n- Консультация по вопросам налогообложения\n- Составление декларации 3-НДФЛ на основании предоставленных данных\n- Ответы на вопросы по заполнению декларации', '3000.00', 1, '2025-05-08 16:54:59', '2025-05-08 16:54:59'),
(17, 'Составление КУДИР и подготовка платежных поручений', 'Составление КУДИР и подготовка платежных поручений для нотариусов и физлиц', 'Профессиональная помощь в составлении Книги учёта доходов и расходов (КУДИР) и подготовке платежных поручений для оплаты налогов и сборов.\n\nЧто входит в услугу:\n- Составление КУДИР по предоставленным данным\n- Подготовка платежных поручений для оплаты налогов\n- Консультации по вопросам заполнения КУДИР и платежных документов', '2000.00', 2, '2025-05-08 16:54:59', '2025-05-08 16:54:59'),
(18, 'Составление документов для ИФНС, сверка и переписка', 'Составление документов для ИФНС, сверка и переписка по личным налогам', 'Комплексная услуга по подготовке документов для ИФНС, сверке расчетов и ведению переписки по вопросам личных налогов физических лиц.\n\nЧто входит в услугу:\n- Составление заявлений, запросов и других документов для ИФНС\n- Сверка расчетов с налоговой инспекцией\n- Переписка и сопровождение клиента в процессе взаимодействия с ИФНС', '5000.00', 3, '2025-05-08 16:54:59', '2025-05-08 16:54:59'),
(19, 'Составление декларации 3-НДФЛ для вычета, разработка сайтов-визиток', 'Составление декларации 3-НДФЛ для вычета и создание сайтов-визиток', 'Подготовка декларации 3-НДФЛ для получения имущественного или социального вычета, а также услуги по разработке современных сайтов-визиток для нотариусов и физических лиц.\n\nЧто входит в услугу:\n- Составление декларации 3-НДФЛ для имущественного/социального вычета\n- Консультации по вопросам получения вычета\n- Разработка и дизайн сайта-визитки «под ключ»\n- Техническая поддержка и сопровождение сайта', '7000.00', 5, '2025-05-08 16:54:59', '2025-05-08 16:54:59');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('user','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(2, 'tigransogomonyan@inbox.ru', '$2y$10$lOGdKsLARCEQT9S5j8cmReV128G4KzZg2zJkuAnwF.5mfLhwKlVly', 'user', '2025-05-08 17:28:11', '2025-05-23 17:32:09'),
(3, 'admin@algoritm.ru', '$2y$10$0Ce3laX2gnkNeBNq2DBqhu/QscJQ0dWy.RpidpqwEk37Qwz6NtKB.', 'admin', '2025-05-23 18:34:53', '2025-05-23 18:35:09');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Индексы таблицы `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
