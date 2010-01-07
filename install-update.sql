ALTER TABLE wcf1_user ADD facebookIdentifierHash varchar(255) NOT NULL AFTER facebookIdentifier ;
ALTER TABLE wcf1_user ADD facebookIdentifierSalt varchar(255) NOT NULL AFTER facebookIdentifierHash ;