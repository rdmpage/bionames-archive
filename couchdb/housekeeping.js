{
   "_id": "_design/housekeeping",
   "_rev": "1-d51a53b5acd14b5ce84e05e93625ab00",
   "language": "javascript",
   "views": {
       "ids": {
           "map": "function(doc) {\n  emit(doc._id, null);\n}"
       }
   }
}