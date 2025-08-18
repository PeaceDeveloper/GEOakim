// MongoDB initialization script
db = db.getSiblingDB('geoakim');

db.createCollection('entries');

// Create indexes for better performance
db.entries.createIndex({ "timestamp": -1 });
db.entries.createIndex({ "ip": 1 });
db.entries.createIndex({ "geo": 1 });

print('Database initialized successfully!');