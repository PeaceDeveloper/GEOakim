db = db.getSiblingDB('geoakim');

db.createCollection('geo_data');
db.createCollection('invite_links');
db.createCollection('app_logs');
db.createCollection('error_logs');
db.createCollection('rate_limits');

db.geo_data.createIndex({ created_at: -1 });
db.geo_data.createIndex({ ip: 1 });
db.geo_data.createIndex({ geo: 1 });
db.geo_data.createIndex({ timestamp: -1 });
db.geo_data.createIndex({ link_uid: 1 });
db.geo_data.createIndex({ link_uid: 1, created_at: -1 });

db.invite_links.createIndex({ uid: 1 }, { unique: true });
db.invite_links.createIndex({ expires_at: 1 });
db.invite_links.createIndex({ revoked_at: 1 });
db.invite_links.createIndex({ created_at: -1 });

db.app_logs.createIndex({ timestamp: -1 });
db.error_logs.createIndex({ timestamp: -1 });
db.rate_limits.createIndex({ key: 1 }, { unique: true });
db.rate_limits.createIndex({ expires_at: 1 }, { expireAfterSeconds: 0 });

db.createUser({
  user: 'geoakim_user',
  pwd: 'secure_password_123',
  roles: [{ role: 'readWrite', db: 'geoakim' }]
});

print('MongoDB initialized successfully for GEOakim application');
