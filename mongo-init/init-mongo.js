// MongoDB initialization script
db = db.getSiblingDB('geoakim');

// Create collections with indexes
db.createCollection('geo_data');
db.createCollection('app_logs');
db.createCollection('error_logs');

// Create indexes for better performance
db.geo_data.createIndex({ "created_at": -1 });
db.geo_data.createIndex({ "ip": 1 });
db.geo_data.createIndex({ "geo": 1 });
db.geo_data.createIndex({ "timestamp": -1 });

db.app_logs.createIndex({ "timestamp": -1 });
db.error_logs.createIndex({ "timestamp": -1 });

// Create a user for the application
db.createUser({
  user: "geoakim_user",
  pwd: "secure_password_123",
  roles: [
    {
      role: "readWrite",
      db: "geoakim"
    }
  ]
});

print("MongoDB initialized successfully for GEOakim application");