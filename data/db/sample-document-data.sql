SET foreign_key_checks = 0;

TRUNCATE TABLE jasper_document;
TRUNCATE TABLE jasper_document_variables;

SET foreign_key_checks = 1;

INSERT INTO jasper_document (id, template_id) VALUES (1, 1);

INSERT INTO jasper_document_variables (document_id, `key`, `value`) VALUES
(1, 'TestNumber', '123456'),
(1, 'VRM', 'AW50MER'),
(1, 'VIN', 'ADFG12313'),
(1, 'Make', 'German'),
(1, 'Model', 'Whip'),
(1, 'Colour', 'Blue'),
(1, 'CountryOfRegistration', 'UK'),
(1, 'IssuersName', 'Bobby Bobson'),
(1, 'TestClass', 'A Class'),
(1, 'Odometer1', '50000'),
(1, 'Odometer2', '25000'),
(1, 'Odometer3', '10000'),
(1, 'ExpiryDate', '25/12/2014'),
(1, 'IssuedDate', '25/12/2013'),
(1, 'TestStation', 'V1234');