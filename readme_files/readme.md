How to Pull and Push in the repository

## Get the latest code (ALWAYS DO THIS FIRST)

```sh
git pull origin main
```

### Make Your Change

After pulling the current repository for its current code. You can now edit and files in your local folder.

### Save your changes

```sh
git add .
git commit -m "Describe what you changed"
```

if you want to specify what file you want to commit specially when adding and editing single file you can do it by doing this.

```sh
git add folder_name/file_nmae
git commit -m "Describe what you changed"
```

### Upload your changes

```sh
git push origin main
```

This send your work in the remote repo.

## WHAT IF SOMEONE ELSE UPDATED main BEFORE YOU

if git push fails because other collaborator made changes before you. Do this

```
git pull --rebase origin main
git push origin main
```

This command updates your code and avoid any merge conflicts

## IF THERE IS A MERGE CONFLICT

Git will show conflicting files. Open them and fix conflicts. Usually VS code will prompt fix merge conflicts just press them and accept the approriate incoming codes.

After fixing the conflicts run this commands

```sh
git add .
git commit -m "Fixed merge conflict"
git push origin main
```

Now, the changes all are uploaded

````how to run tailwind css in terminal

npx tailwindcss -i ./src/input.css -o ./public/output.css --watch


``` how to run php in localhost
php -S localhost:8000
````
