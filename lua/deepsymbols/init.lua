local get_symbols = function()
    local file = vim.fn.expand('%:p')
    local path = vim.fn.getcwd()

    local command = 'deep-symbols ' .. path .. ' ' .. file

    if pcall(require, 'telescope') then
        local actions = require('telescope.actions')
        local action_state = require('telescope.actions.state')
        local finders = require('telescope.finders')
        local pickers = require('telescope.pickers')
        local conf = require('telescope.config').values

        -- Define a finder using the command output
        local finder = finders.new_oneshot_job(
            {'sh', '-c', command},
            {
                entry_maker = function(entry)
                    local filename, line, type, desc = entry:match("^(.-):(.-):%[(.-)%] (.*)$")
                    line = tonumber(line)  -- Convert line number to a number
                    return {
                        value = entry,
                        display = '[' .. type .. '] ' .. desc,
                        ordinal = desc .. ' ' .. filename .. ':' .. line,
                        lnum = line,
                        filename = filename,
                    }
                end
            }
        )

        -- Define the picker configuration
        local picker = pickers.new({}, {
            prompt_title = 'Deep Symbols',
            finder = finder,
            sorter = conf.generic_sorter({}),
            previewer = conf.grep_previewer({}),
            attach_mappings = function()
                actions.select_default:replace(function(prompt_bufnr)
                    local selection = action_state.get_selected_entry()
                    actions.close(prompt_bufnr)
                    vim.cmd('e ' .. selection.filename)  -- Open the file
                    vim.cmd(':' .. selection.lnum)      -- Go to the line
                    vim.cmd('normal zz')  -- Center the line in the window
                end)
                return true
            end
        })

        -- Launch the picker
        picker:find()
    else
        -- Fallback to fzf-lua
        require('fzf-lua').files({ cmd = command, fzf_args = '--delimiter=":" --with-nth=-1' })
    end
end

return {
    get_symbols = get_symbols
}


