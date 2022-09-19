# Document Symbols Improved

A simple neovim plugin to display document symbols with inherited members.

# Problem
The current [LSP document symbols](https://microsoft.github.io/language-server-protocol/specifications/lsp/3.17/specification/#textDocument_documentSymbol) does not consider inherted members. which is something I used to have with [PHPStorm](https://www.jetbrains.com/phpstorm/promo/?source=ahmedash95.github.io) and had saved me alot navigating the code. and as I'm moving to Neovim. I missed that feature and after some looking and searching I could not find any good replacement. so I decided to make it myself. 

the project scope should and always will be small. focusing only on PHP as it's my primary language. and would just change to serve future versions of PHP. 


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
With this plugin, it would show you all the members and inherited members of the class you are currently in.

<img src="preview.png">

### Install

First you need to install deep-symbol globally from your composer

```
composer global require ahmedash95/deep-symbols
```

Make sure that your global composer directory is inside of your PATH environment variable. Simply add this directory to your PATH in your ~/.bash_profile (or ~/.bashrc) like this:
```
export PATH=~/.composer/vendor/bin:$PATH
```

once installed you can verifiy if its working by opening a new terminal session and type 
```
$ deep-symbols
```

### Plugin

Using vim-plug
```vim
Plug 'ahmedash95/deep-symbols'
```

Using packer.nvim
```lua
use 'ahmedash95/deep-symbols'
```

### Usage

you can run it with the command
```
:lua require("deepsymbols").get_symbols()

-- or mapping

vim.keymap.set('n', '<leader>o', ':lua require("deepsymbols").get_symbols()<CR>')
```


