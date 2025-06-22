-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 22, 2025 at 03:07 AM
-- Server version: 10.6.22-MariaDB-cll-lve
-- PHP Version: 8.3.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `zingecll_blocks_zinggati`
--

-- --------------------------------------------------------

--
-- Table structure for table `block_types`
--

CREATE TABLE `block_types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(50) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `block_types`
--

INSERT INTO `block_types` (`id`, `type_name`, `unit_price`) VALUES
(1, '6 inch', 10.00),
(2, '4 inch', 9.00),
(3, '8 inch', 15.00);

-- --------------------------------------------------------

--
-- Table structure for table `clients_orders`
--

CREATE TABLE `clients_orders` (
  `id` int(11) NOT NULL,
  `block_type_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` > 0),
  `order_date` date DEFAULT NULL,
  `status` enum('Pending','Completed','Cancelled') DEFAULT NULL,
  `payment_method` enum('Cash','Mobile Money') NOT NULL DEFAULT 'Cash',
  `client_name` varchar(100) DEFAULT NULL,
  `contact` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients_orders`
--

INSERT INTO `clients_orders` (`id`, `block_type_id`, `quantity`, `order_date`, `status`, `payment_method`, `client_name`, `contact`) VALUES
(1, 1, 20, '2025-05-24', 'Completed', 'Cash', 'Rsaf', '0962088185');

--
-- Triggers `clients_orders`
--
DELIMITER $$
CREATE TRIGGER `after_order_insert` AFTER INSERT ON `clients_orders` FOR EACH ROW BEGIN
    DECLARE block_type_name VARCHAR(50);
    DECLARE block_unit_price DECIMAL(10,2); -- Add this variable

    -- Fetch BOTH type_name and unit_price from block_types
    SELECT type_name, unit_price INTO block_type_name, block_unit_price
    FROM block_types 
    WHERE id = NEW.block_type_id;

    -- Use block_unit_price instead of NEW.unit_price
    INSERT INTO transactions (
        date, 
        description, 
        category, 
        type, 
        amount, 
        method,
        notes
    ) VALUES (
        NEW.order_date,
        CONCAT('Order for ', block_type_name, ' blocks'),
        'Block Sales',
        'Income',
        NEW.quantity * block_unit_price, -- Critical fix here
        NEW.payment_method,
        CONCAT('Order ID: ', NEW.id, ' | Client: ', NEW.client_name)
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `client_collections`
--

CREATE TABLE `client_collections` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `blocks_collected` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daily_production`
--

CREATE TABLE `daily_production` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `block_type_id` int(11) NOT NULL,
  `blocks_produced` int(11) NOT NULL,
  `cement_bags_used` int(11) NOT NULL,
  `fuel_liters` decimal(10,2) DEFAULT NULL,
  `waste_blocks` int(11) DEFAULT 0,
  `production_cost` decimal(12,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daily_production`
--

INSERT INTO `daily_production` (`id`, `date`, `block_type_id`, `blocks_produced`, `cement_bags_used`, `fuel_liters`, `waste_blocks`, `production_cost`) VALUES
(1, '2025-05-24', 1, 500, 5, 5.00, 0, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `installation_date` date NOT NULL,
  `last_maintenance` date DEFAULT NULL,
  `maintenance_interval` int(11) DEFAULT 90
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment_maintenance`
--

CREATE TABLE `equipment_maintenance` (
  `id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `maintenance_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `cost` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` int(11) NOT NULL,
  `material_name` varchar(100) DEFAULT NULL,
  `quantity_remaining` int(10) DEFAULT NULL,
  `unit` varchar(10) DEFAULT NULL,
  `last_updated` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`id`, `material_name`, `quantity_remaining`, `unit`, `last_updated`) VALUES
(6, 'Cement', 15, 'bags', '2025-05-24'),
(7, '5mm Stone', 80, 'tons', NULL),
(8, 'Fuel', 20, 'liters', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `material_usage`
--

CREATE TABLE `material_usage` (
  `production_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `quantity_used` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `material_usage`
--
DELIMITER $$
CREATE TRIGGER `after_material_usage_insert` AFTER INSERT ON `material_usage` FOR EACH ROW BEGIN
    UPDATE materials 
    SET quantity_remaining = quantity_remaining - NEW.quantity_used
    WHERE id = NEW.material_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `production_targets`
--

CREATE TABLE `production_targets` (
  `id` int(11) NOT NULL,
  `target_date` date NOT NULL,
  `block_type_id` int(11) NOT NULL,
  `target_quantity` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `progress` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_targets`
--

INSERT INTO `production_targets` (`id`, `target_date`, `block_type_id`, `target_quantity`, `created_at`, `progress`) VALUES
(1, '2025-05-12', 1, 1500, '2025-04-21 08:24:15', 0);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `payment_status` enum('Paid','Unpaid') DEFAULT NULL,
  `last_payment` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_daily_log`
--

CREATE TABLE `staff_daily_log` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `type` enum('Income','Expense') DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `method` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `date`, `description`, `category`, `type`, `amount`, `method`, `notes`) VALUES
(1, '2025-05-10', 'bearings and round clips ', NULL, 'Expense', 4000.00, 'Cash', 'pallet and motor dispenser broke down due to not being oiled regularly. '),
(2, '2025-05-10', 'bearings and round clips ', NULL, 'Expense', 4000.00, 'Cash', 'pallet and motor dispenser broke down due to not being oiled regularly. '),
(3, '2025-05-13', 'Motor Replacement', NULL, 'Expense', 8500.00, 'Mobile Money', 'Mixer Motor broke down and needed to be replaced.'),
(4, '2025-05-24', 'Order for 6 inch blocks', 'Block Sales', 'Income', 200.00, 'Cash', 'Order ID: 1 | Client: Rsaf');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','accounts','it','manager','worker') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'muzo', '$2y$10$Q2TfVh/s4NJuS94lhDv2huaT8bF.zxcDJqTmVvW7eRUWm2GUpo9uO', 'admin'),
(2, 'nickchansa2465', '$2y$10$WuY39EeArZndGECnqoXDsu2einzdYc6nTdI/CF0GQ8jnvcN.nhLLG', 'admin'),
(3, 'maggie', '$2y$10$Fx/SERusY5x7K3fkbwd3GuXH6SmkLu5JcrCJbZro.AD4pQcWRCcQC', 'manager'),
(4, 'incharge', '$2y$10$ltPRjQ.IVymDugKkJFabqeDW2ATyuFsZatoNxR1GNqVXV4KkmGU3q', 'worker'),
(6, 'Emcy', '$2y$10$M4PO/Cw1fbwGJ5h1.R6N4eTKWACIde4a6NlX2q9Ze.NnLzwV.jh2W', 'manager');

-- --------------------------------------------------------

--
-- Table structure for table `worker_schedules`
--

CREATE TABLE `worker_schedules` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `schedule_date` date NOT NULL,
  `task_description` text NOT NULL,
  `shift_start` time NOT NULL,
  `shift_end` time NOT NULL,
  `status` enum('scheduled','completed','cancelled') DEFAULT 'scheduled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `block_types`
--
ALTER TABLE `block_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- Indexes for table `clients_orders`
--
ALTER TABLE `clients_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `block_type_id` (`block_type_id`);

--
-- Indexes for table `client_collections`
--
ALTER TABLE `client_collections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `daily_production`
--
ALTER TABLE `daily_production`
  ADD PRIMARY KEY (`id`),
  ADD KEY `block_type_id` (`block_type_id`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `equipment_maintenance`
--
ALTER TABLE `equipment_maintenance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipment_id` (`equipment_id`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `material_usage`
--
ALTER TABLE `material_usage`
  ADD PRIMARY KEY (`production_id`,`material_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indexes for table `production_targets`
--
ALTER TABLE `production_targets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `block_type_id` (`block_type_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff_daily_log`
--
ALTER TABLE `staff_daily_log`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance` (`staff_id`,`date`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `worker_schedules`
--
ALTER TABLE `worker_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `block_types`
--
ALTER TABLE `block_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `clients_orders`
--
ALTER TABLE `clients_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `client_collections`
--
ALTER TABLE `client_collections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `daily_production`
--
ALTER TABLE `daily_production`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipment_maintenance`
--
ALTER TABLE `equipment_maintenance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `production_targets`
--
ALTER TABLE `production_targets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `staff_daily_log`
--
ALTER TABLE `staff_daily_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `worker_schedules`
--
ALTER TABLE `worker_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `clients_orders`
--
ALTER TABLE `clients_orders`
  ADD CONSTRAINT `clients_orders_ibfk_1` FOREIGN KEY (`block_type_id`) REFERENCES `block_types` (`id`),
  ADD CONSTRAINT `clients_orders_ibfk_2` FOREIGN KEY (`block_type_id`) REFERENCES `block_types` (`id`);

--
-- Constraints for table `client_collections`
--
ALTER TABLE `client_collections`
  ADD CONSTRAINT `client_collections_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `clients_orders` (`id`);

--
-- Constraints for table `daily_production`
--
ALTER TABLE `daily_production`
  ADD CONSTRAINT `daily_production_ibfk_1` FOREIGN KEY (`block_type_id`) REFERENCES `block_types` (`id`);

--
-- Constraints for table `equipment_maintenance`
--
ALTER TABLE `equipment_maintenance`
  ADD CONSTRAINT `equipment_maintenance_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`);

--
-- Constraints for table `material_usage`
--
ALTER TABLE `material_usage`
  ADD CONSTRAINT `material_usage_ibfk_1` FOREIGN KEY (`production_id`) REFERENCES `daily_production` (`id`),
  ADD CONSTRAINT `material_usage_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`);

--
-- Constraints for table `production_targets`
--
ALTER TABLE `production_targets`
  ADD CONSTRAINT `production_targets_ibfk_1` FOREIGN KEY (`block_type_id`) REFERENCES `block_types` (`id`);

--
-- Constraints for table `staff_daily_log`
--
ALTER TABLE `staff_daily_log`
  ADD CONSTRAINT `staff_daily_log_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`);

--
-- Constraints for table `worker_schedules`
--
ALTER TABLE `worker_schedules`
  ADD CONSTRAINT `worker_schedules_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
