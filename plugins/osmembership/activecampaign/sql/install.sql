CREATE TABLE IF NOT EXISTS #__osmembership_activecampaign (
  id int UNSIGNED NOT NULL AUTO_INCREMENT,
  contact_id int UNSIGNED NOT NULL DEFAULT 0,
  tag_id int UNSIGNED NOT NULL DEFAULT 0,
  contact_tag_id int UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY(id)
);