
CREATE TABLE `customer` (
  `CustomerID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `Phone` varchar(30) DEFAULT NULL,
  `Role` enum('customer','producer') DEFAULT 'customer',
  `LoyaltyPoints` int(11) DEFAULT 0,
  `CreatedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--

INSERT INTO `customer` (`CustomerID`, `Name`, `Email`, `Password`, `Address`, `Phone`, `Role`, `LoyaltyPoints`, `CreatedAt`) VALUES
(2, 'CherryAve Farm', 'cherryave@glh.co.uk', '$2y$10$abcdefghijklmnopqrstuuAbCdEfGhIjKlMnOpQrStUvWxYz12345', NULL, NULL, 'producer', 0, '2026-04-20 10:45:17'),
(3, 'Treeside Farm', 'treeside@glh.co.uk', '$2y$10$abcdefghijklmnopqrstuuAbCdEfGhIjKlMnOpQrStUvWxYz12345', NULL, NULL, 'producer', 0, '2026-04-20 10:45:17'),
(4, 'John smith', 'Johnsmith@gmail.com', '$2y$10$OwsHkjXsQmAEEb9TCbOSb.N9n.tpnsDCobm9VTsRLFqVloOlB7Ngi', NULL, NULL, 'customer', 42, '2026-04-20 10:48:51'),
(5, 'Hillside Farm', 'hillside@glh.co.uk', '$2y$10$tTAyjDfX1DeB6neFn6ksCOFwM78/IkDjYeZMYYiK4USgttgu1puOW', NULL, NULL, 'producer', 0, '2026-04-20 10:51:08');



CREATE TABLE `loyaltypoints` (
  `TransactionID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `Points` int(11) NOT NULL,
  `TransactionDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `loyaltypoints` (`TransactionID`, `CustomerID`, `Points`, `TransactionDate`) VALUES
(1, 4, 42, '2026-04-20 10:49:26');


CREATE TABLE `order` (
  `OrderID` int(11) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `OrderDate` datetime DEFAULT current_timestamp(),
  `DeliveryType` enum('Collection','Delivery') NOT NULL,
  `OrderStatus` enum('Pending','Confirmed','Shipped','Completed','Cancelled') DEFAULT 'Pending',
  `TotalPrice` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `order` (`OrderID`, `CustomerID`, `OrderDate`, `DeliveryType`, `OrderStatus`, `TotalPrice`) VALUES
(1, 4, '2026-04-20 10:49:26', 'Delivery', 'Shipped', 4.20);



CREATE TABLE `orderitem` (
  `OrderItemID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `ProductID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `Price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `orderitem` (`OrderItemID`, `OrderID`, `ProductID`, `Quantity`, `Price`) VALUES
(1, 1, 2, 1, 0.90),
(2, 1, 4, 1, 0.20),
(3, 1, 1, 1, 1.60),
(4, 1, 3, 1, 0.50);



CREATE TABLE `producer` (
  `ProducerID` int(11) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `Name` varchar(100) NOT NULL,
  `FarmLocation` varchar(255) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `ContactEmail` varchar(255) DEFAULT NULL,
  `ImageURL` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `producer` (`ProducerID`, `CustomerID`, `Name`, `FarmLocation`, `Description`, `ContactEmail`, `ImageURL`) VALUES
(1, 5, 'Hillside Farm', 'Greendale Valley, 3 miles', 'Joe and Henry are members of the Green Local Hub co-op and run their own market garden out at Greendale Valley. They specialise in seasonal root vegetables grown using traditional organic methods.', 'hillside@glh.co.uk', 'hillside-farm.jpg'),
(2, 2, 'CherryAve Farm', 'Cherry Avenue, 5 miles', 'Trevor and Micheal are members of the Green Local Hub co-op and run their own organic events out at Cherry Avenue. They focus on salad leaves and herbs grown in polytunnels.', 'cherryave@glh.co.uk', 'cherryave-farm.jpg'),
(3, 3, 'Treeside Farm', 'Treeside Lane, 7 miles', 'Glorious vegetables from the team at Treeside. This is all about growing seasonal, organic vegetables, salad, herbs and more using sustainable farming practices.', 'treeside@glh.co.uk', 'treeside-farm.jpg');

-- --------------------------------------------------------


CREATE TABLE `product` (
  `ProductID` int(11) NOT NULL,
  `ProducerID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  `StockLevel` int(11) DEFAULT 0,
  `Description` text DEFAULT NULL,
  `ImageURL` varchar(255) DEFAULT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `product` (`ProductID`, `ProducerID`, `Name`, `Price`, `StockLevel`, `Description`, `ImageURL`, `CreatedAt`) VALUES
(1, 1, 'Organic Potatoes', 1.60, 24, 'Fresh organic potatoes from Hillside Farm', 'potatoes.jpg', '2026-04-20 10:45:17'),
(2, 1, 'Carrots', 0.90, 29, 'Locally grown carrots from Hillside Farm', 'carrots.jpg', '2026-04-20 10:45:17'),
(3, 1, 'Spring Onions', 0.50, 3, 'Fresh spring onions from Hillside Farm', 'spring-onions.jpg', '2026-04-20 10:45:17'),
(4, 2, 'Tomatoes', 0.20, 39, 'Vine ripened tomatoes from CherryAve Farm', 'tomatoes.jpg', '2026-04-20 10:45:17'),
(5, 2, 'Lettuce', 0.80, 3, 'Crisp organic lettuce from CherryAve Farm', 'lettuce.jpg', '2026-04-20 10:45:17'),
(6, 3, 'Free Range Eggs', 2.50, 20, 'Free range eggs from Treeside Farm', 'eggs.jpg', '2026-04-20 10:45:17');


ALTER TABLE `customer`
  ADD PRIMARY KEY (`CustomerID`),
  ADD UNIQUE KEY `Email` (`Email`);


ALTER TABLE `loyaltypoints`
  ADD PRIMARY KEY (`TransactionID`),
  ADD KEY `CustomerID` (`CustomerID`);


ALTER TABLE `order`
  ADD PRIMARY KEY (`OrderID`),
  ADD KEY `CustomerID` (`CustomerID`);


ALTER TABLE `orderitem`
  ADD PRIMARY KEY (`OrderItemID`),
  ADD KEY `OrderID` (`OrderID`),
  ADD KEY `ProductID` (`ProductID`);


ALTER TABLE `producer`
  ADD PRIMARY KEY (`ProducerID`),
  ADD KEY `CustomerID` (`CustomerID`);

ALTER TABLE `product`
  ADD PRIMARY KEY (`ProductID`),
  ADD KEY `ProducerID` (`ProducerID`);



ALTER TABLE `customer`
  MODIFY `CustomerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `loyaltypoints`
  MODIFY `TransactionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;


ALTER TABLE `order`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;


ALTER TABLE `orderitem`
  MODIFY `OrderItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;


ALTER TABLE `producer`
  MODIFY `ProducerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;


ALTER TABLE `product`
  MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;


ALTER TABLE `loyaltypoints`
  ADD CONSTRAINT `loyaltypoints_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`);


ALTER TABLE `order`
  ADD CONSTRAINT `order_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`);


ALTER TABLE `orderitem`
  ADD CONSTRAINT `orderitem_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `order` (`OrderID`),
  ADD CONSTRAINT `orderitem_ibfk_2` FOREIGN KEY (`ProductID`) REFERENCES `product` (`ProductID`);

ALTER TABLE `producer`
  ADD CONSTRAINT `producer_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`);


ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`ProducerID`) REFERENCES `producer` (`ProducerID`);
COMMIT;
