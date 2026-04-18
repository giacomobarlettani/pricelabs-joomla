ALTER TABLE `#__e4jconnect_pricelabs_jobs`
ADD COLUMN IF NOT EXISTS `completedon` datetime DEFAULT NULL AFTER `executedon`;