# List of files

## How do we know which part that we edited in a 3rd-party plugin files?

- Open a file you want to check.
- At the same directory, there should be the same file name but prefix with `.orig`, copy content from `.orig` prefixed to the previous file you opened.
- Save, and do `git diff` at your terminal program. It will tell you which lines that have been edited, removed and added.

v10.4
- js/frontend-address-form-ui.js
- js/frontend-address-form.js
