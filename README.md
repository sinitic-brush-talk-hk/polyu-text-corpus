# Sinitic Brushtalk - A corpus of Miyazaki Touten’s family collection

## Making minor edits

1. Download and edit `data/data.sqlite3` file using [DB Browser for SQLite](https://sqlitebrowser.org/) or other tool.

2. Upload back to GitHub using "Add file" > "Upload files", then press commit.

## Deployment

Any commits made to this repository will automatically be deployed to https://sinitic-brush-talk-hk.github.io/polyu-text-corpus/ via GitHub actions.

To deploy to an external server, download the generated zip file in the “Artifacts” section of the newest workflow run on the actions tab.

## Development

When developing locally, you need to regenerate the static website after making any changes.

1. Clone from GitHub to a local directory.

2. Run in terminal:

   ```
   cd path/to/folder/polyu-text-corpus
   php -S localhost:8004 -t .
   ```

3. Visit `http://localhost:8004/`.

4. Click on 'Build and download zip'.

5. Upload contents of the zip to destination server (if required), or preview the changes at `http://localhost:8004/build/`.
