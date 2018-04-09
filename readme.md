# Usage

```
php index.php diff-csv --source_file=csv_documents/doc1.csv --source_header=Barcode --compare_with=csv_documents/doc2.csv --compare_header=barcode
```

A new fill will be created with all rows from `doc1` that are not present in `doc2` but it only checks a single column.
