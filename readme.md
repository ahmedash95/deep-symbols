# Document Symbols Improved

A simple neovim plugin to display document symbols with inherited members.

## Prerequisites
- Neovim
- [FZFLua](https://github.com/ibhagwan/fzf-lua)
- PHP
- Composer

## Preview
Let's say in your project you have 2 models Admin.php and User.php
```php
class User
{
    public function getName() {
        return 'John';
    }
}

class Admin extends User
{
    public function getAdminRole() {
        return 'supervisor';
    }
}
```
What this plugin does is it shows you all the members and inherited members of the class you are currently in.

<img src="preview.png">

### TBD