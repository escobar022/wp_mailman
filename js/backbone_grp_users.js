/**
 * Created by AEscobar on 8/18/15.
 */
(function ($) {

	console.log('hioo');

	var wpm_users = window.wp_GrpUsers;

	var users_in_group = wpm_users.users_in_group_info;
	var users_available = wpm_users.users_available_info;



	var UserInfo = Backbone.Model.extend({
		defaults: {
			'userID'   : 'undefined',
			'userEmail': 'undefined'
		}
	});

	UserInfo.View = Backbone.View.extend({
		className: 'user_info',
		render    : function () {
			var template = _.template($("#users-current-group").html());
			this.$el.html(template(this.model.attributes));
			return this;
		}
	});

	///collection
	var CurrentUsers = Backbone.Collection.extend({
		model: UserInfo
	});

	var users_current = new CurrentUsers(users_in_group);
	var users_available_add = new CurrentUsers(users_available);
	console.log(JSON.stringify(users_current));


	var UsersInGroup = Backbone.View.extend({
		el        : '#group_users',
		initialize: function () {
			this.render();
		},
		render: function() {

			var items = this.collection.models;

			_.each(items, function (item) {
				var itemView = new UserInfo.View({model: item});

				console.log(itemView);
				this.$el.append(itemView.render().el);
			}, this);
		}

	});


	var all_users_in_group = new UsersInGroup({collection:users_current});

	var AllUsers = Backbone.View.extend({
		el        : '#all_users',
		initialize: function () {
			this.render();
		},
		render: function() {

			var items = this.collection.models;

			_.each(items, function (item) {
				var itemView = new UserInfo.View({model: item});

				console.log(itemView);
				this.$el.append(itemView.render().el);
			}, this);
		}

	});

	var all_users = new AllUsers({collection:users_available_add});




}(jQuery));
