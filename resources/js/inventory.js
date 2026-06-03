window.openDeleteModal = function(url)
{
    document
        .getElementById('deleteForm')
        .action = url;

    document
        .getElementById('deleteModal')
        .classList.remove('hidden');

    document
        .getElementById('deleteModal')
        .classList.add('flex');
}

window.closeDeleteModal = function()
{
    document
        .getElementById('deleteModal')
        .classList.remove('flex');

    document
        .getElementById('deleteModal')
        .classList.add('hidden');
}

const searchInput =
document.getElementById(
    'search'
);

const searchForm =
document.getElementById(
    'searchForm'
);

if(searchInput && searchForm)
{
    let timeout;
    searchInput.addEventListener(
        'keyup',
        function()
        {
            clearTimeout(timeout);

            timeout = setTimeout(
                () =>
                {
                    searchForm.submit();
                },
                300
            );
        }
    );
}

const categoryFilter =
document.getElementById(
    'categoryFilter'
);

if(categoryFilter && searchForm)
{
    categoryFilter.addEventListener(
        'change',
        function()
        {
            searchForm.submit();
        }
    );
}