<?php
	// +----------------------------------------------------------------------
	// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
	// +----------------------------------------------------------------------
	// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
	// +----------------------------------------------------------------------
	// | Author: pl125 <xskjs888@163.com>
	// +----------------------------------------------------------------------

	namespace api\portal\model;

	use api\common\model\ParamsFilterModel;

	class PortalPostModel extends ParamsFilterModel
	{
		//可查询字段
		protected $visible = [
			'id', 'articles.id', 'user_id', 'post_id', 'post_type', 'comment_status', 'is_top',
			'recommended', 'post_hits', 'post_like', 'comment_count', 'create_time',
			'update_time', 'published_time', 'post_title', 'post_keywords',
			'post_excerpt', 'post_source', 'post_content', 'more','user_nickname'
		];
		//模型关联方法
		protected $relationFilter = ['user'];

		/**
		 * 基础查询
		 */
		protected function base($query)
		{
			$query->where('delete_time', 0)
				->where('post_status', 1)
				->whereTime('published_time', 'between', [1, time()]);
		}

		/**
		 * post_content 自动转化
		 * @param $value
		 * @return string
		 */
		public function getPostContentAttr($value)
		{
			return cmf_replace_content_file_url(htmlspecialchars_decode($value));
		}

		/**
		 * more 自动转化
		 * @param $value
		 * @return array
		 */
		public function getMoreAttr($value)
		{
			$more = json_decode($value, true);
			if (!empty($more['thumbnail'])) {
				$more['thumbnail'] = cmf_get_image_url($more['thumbnail']);
			}

			if (!empty($more['photos'])) {
				foreach ($more['photos'] as $key => $value) {
					$more['photos'][$key]['url'] = cmf_get_image_url($value['url']);
				}
			}

			if (!empty($more['files'])) {
				foreach ($more['files'] as $key => $value) {
					$more['files'][$key]['url'] = cmf_get_image_url($value['url']);
				}
			}
			return $more;
		}

		/**
		 * 关联 user表
		 * @return $this
		 */
		public function user()
		{
			return $this->belongsTo('UserModel','user_id');
		}
		/**
		 * 关联 user表
		 * @return $this
		 */
		public function articleUser()
		{
			return $this->belongsTo('UserModel','user_id')->field('id,user_nickname');
		}
		/**
		 * 获取相关文章
		 * @param int|string|array $postId 文章id
		 * @return array
		 */
		public function getRelationPosts($postIds)
		{
			$posts = $this->with('articleUser')
						->field('id,post_title,user_id,is_top,post_hits,post_like,comment_count,more')
						->whereIn('id',$postIds)
						->select();
			foreach ($posts as $post) {
				$post ->appendRelationAttr('articleUser','user_nickname');
			}
			return $posts;
		}
	}
