local get_symbols = function()
    local file = vim.fn.expand('%')
    local path = vim.fn.getcwd() 

    local command = 'php /Users/ahmed/code/vim-structure-ff/index.php ' .. path .. ' ' .. file

    require('fzf-lua').files({ cmd = command, fzf_args = '--delimiter=":" --with-nth=-1' })
end

return {
    get_symbols = get_symbols
}
