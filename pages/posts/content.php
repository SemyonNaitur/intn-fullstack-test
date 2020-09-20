<div id="postsContent" class="container mt-5 loading-wrap">

    <div class="card">
        <div class="card-body">

            <div class="form-inline mb-3">
                <div class="form-group">
                    <button class="btn btn-primary" data-action="fetch-data">Fetch Remote Data</button>
                </div>
            </div>

            <div class="form-inline">
                <div class="form-group">
                    <label>Search By</label>
                </div>
                <div class="form-group">
                    <select class="form-control" data-input="search-by">
                        <option value="id">id</option>
                        <option value="user_id">user id</option>
                        <option value="content">content</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" data-input="search-param">
                </div>
                <div class="form-group">
                    <button class="btn btn-primary" data-action="search">Search</button>
                </div>
            </div>

        </div>
    </div>

</div>