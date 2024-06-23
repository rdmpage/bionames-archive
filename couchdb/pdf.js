{
   "_id": "_design/pdf",
   "_rev": "1-d1f8323ef104c97c901c86f22ba8b4eb",
   "language": "javascript",
   "views": {
       "url": {
           "map": "function(doc) \n{\n  if (doc.urls)\n  {\n    for(var i in doc.urls)\n    {\n      emit(doc.urls[i], doc._id);\n    }\n  }\n}"
       }
   }
}