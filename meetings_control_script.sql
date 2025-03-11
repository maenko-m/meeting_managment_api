-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Мар 11 2025 г., 16:07
-- Версия сервера: 8.0.30
-- Версия PHP: 8.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `meetings_control`
--

--
-- Дамп данных таблицы `employee`
--

INSERT INTO `employee` (`id`, `organization_id`, `name`, `surname`, `patronymic`, `email`, `password`, `roles`) VALUES
(1, 1, 'иванов', 'иван', 'иванович', 'test@test.test', '$2y$13$Z75dgP1d.n57CB/vynA4feiiYaJNgFOU0ONJGqwSyVGSTtY77rD1G', '[\"ROLE_USER\"]'),
(2, 1, 'Тест', 'Тест', 'Тест', 'none', 'test2', '[]'),
(3, 1, 'valeraEDITED', 'valera', 'alera', 'valera@a.ru', '$2y$13$/ZM5R8kRx1.ymsLdY3Zxved6ol0vbp2crzjvdNP4WD2ZiRzE47af2', '[\"ROLE_USER\"]'),
(4, 1, 'admin', 'admin', 'admin', 'admin@gmail.ru', '$2y$13$OivNqTs/CXaeDTLgMJr2Je.9VzHpB9xsXfAZQs48VFodNahK0.XWC', '[\"ROLE_MODERATOR\"]');

--
-- Дамп данных таблицы `event`
--

INSERT INTO `event` (`id`, `employee_id`, `name`, `description`, `date`, `time_start`, `time_end`, `meeting_room_id`) VALUES
(1, 1, 'Тестовое событие', 'none', '2025-02-26', '20:00:00', '21:00:00', 1),
(2, 1, 'fromjsEDITED', 'fromjsEDITED', '2025-02-27', '00:20:00', '17:20:00', 1),
(4, 1, 'EDITED', 'EDITED', '2025-02-27', '10:20:00', '17:20:00', 1);

--
-- Дамп данных таблицы `event_employee`
--

INSERT INTO `event_employee` (`event_id`, `employee_id`) VALUES
(1, 2),
(2, 2),
(4, 2);

--
-- Дамп данных таблицы `meeting_room`
--

INSERT INTO `meeting_room` (`id`, `office_id`, `name`, `description`, `photo_path`, `size`, `status`, `calendar_code`, `is_public`) VALUES
(1, 1, 'Комната 213', 'none', 'none', 8, 'активный', 1, 1);

--
-- Дамп данных таблицы `office`
--

INSERT INTO `office` (`id`, `organization_id`, `name`, `city`, `time_zone`) VALUES
(1, 1, 'Екатеринбург', 'Екатеринбург', 5),
(2, 2, 'ТЕСТ', 'ТЕСТОВЫЙ', 0);

--
-- Дамп данных таблицы `organization`
--

INSERT INTO `organization` (`id`, `name`, `status`) VALUES
(1, 'НАГ', 'активный'),
(2, 'ТЕСТ', 'неактивный');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
