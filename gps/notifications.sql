CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mobileno` varchar(10) NOT NULL,
  `notificno` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` varchar(255) NOT NULL,
  `sendtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deliverstatus` int(11) DEFAULT NULL,
  `delivertime` datetime DEFAULT NULL,
  `readstatus` int(11) DEFAULT NULL,
  `readtime` datetime DEFAULT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `mobileno`, `notificno`, `title`, `message`, `sendtime`, `deliverstatus`, `delivertime`, `readstatus`, `readtime`, `status`) VALUES
(1, '8290952769', 101, 'Demo Notification', 'Hello Shivi', '2017-07-27 18:30:00', NULL, NULL, NULL, NULL, 0),
(2, '9897671678', 102, 'Demo 2', 'how are you?', '2017-07-28 00:55:00', NULL, NULL, NULL, NULL, 0);