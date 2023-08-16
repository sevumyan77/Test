CREATE TABLE sent_urls
(
    id           INT( 11 ) AUTO_INCREMENT ,
    url          VARCHAR(255) NOT NULL,
    created_date DATETIME     NOT NULL,
    PRIMARY KEY ( id )
);