UPDATE users SET backend='native' WHERE backend='';

UPDATE options SET value=6 WHERE name='schema_version';
