DROP TABLE IF EXISTS wcf1_user_to_facebook;
CREATE TABLE wcf1_user_to_facebook (
	userID VARCHAR(30) NOT NULL,
	facebookID INT(10) NOT NULL,
	UNIQUE(userID),
	UNIQUE(facebookID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO wcf1_user_to_facebook SELECT userID, facebookIdentifier FROM wcf1_user WHERE facebookIdentifier != "";

ALTER TABLE wcf1_user DROP COLUMN facebookIdentifier, DROP COLUMN facebookIdentifierHash, DROP COLUMN facebookIdentifierSalt;