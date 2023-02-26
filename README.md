# Sinitic Brushtalk - A corpus of Miyazaki Touten’s family collection

## Making minor edits

1. Download and edit `data/data.sqlite3` file using [DB Browser for SQLite](https://sqlitebrowser.org/) or other tool.

2. Upload back to GitHub using "Add file" > "Upload files", then press commit.

## Build

You need to regenerate the static website after making any changes.

1. Clone from GitHub to a local directory.

2. Run in terminal:

   ```
   cd path/to/folder/polyu-text-corpus
   php -S localhost:8004 -t .
   ```

3. Visit `http://localhost:8004/`.

4. Click on 'Build and download zip'.

5. Upload contents of the zip to destination server.
