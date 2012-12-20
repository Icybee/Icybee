<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Comments;

use Textmark_Parser;

/**
 * A comment.
 *
 * @property-read string $absolute_url URL of the comment.
 * @property-read string $author_icon URL of the author's Gravatar.
 * @property-read string $css_class A suitable string for the HTML `class` attribute.
 * @property-read array $css_class_names CSS class names.
 * @property-read string $excerpt HTML excerpt of the comment, made of the first 55 words.
 * @property-read bool $is_author `true` if the author of the comment is the author of the attached node.
 * @property-read \Icybee\Modules\Nodes\Node $node The node the comment is attached to.
 * @property-read string $url URL of the comment relative to the website.
 */
class Comment extends \ICanBoogie\ActiveRecord
{
	const COMMENTID = 'commentid';
	const NID = 'nid';
	const PARENTID = 'parentid';
	const UID = 'uid';
	const AUTHOR = 'author';
	const AUTHOR_EMAIL = 'author_email';
	const AUTHOR_URL = 'author_url';
	const AUTHOR_IP = 'author_ip';
	const CONTENTS = 'contents';
	const STATUS = 'status';
	const NOTIFY = 'notify';
	const CREATED = 'created';

	/**
	 * Comment identifier.
	 *
	 * @var int
	 */
	public $commentid;

	/**
	 * Node identifier.
	 *
	 * @var int
	 */
	public $nid;

	/**
	 * Parent comment identifier.
	 *
	 * @var int
	 */
	public $parentid;

	/**
	 * User identifier.
	 *
	 * The user identifier is zero (0) if the user is a guest.
	 *
	 * @var int
	 */
	public $uid;

	/**
	 * Author name.
	 *
	 * @var string
	 */
	public $author;

	/**
	 * Author email.
	 *
	 * @var string
	 */
	public $author_email;

	/**
	 * Author's website URL.
	 *
	 * @var string
	 */
	public $author_url;

	/**
	 * Author IP.
	 *
	 * @var string
	 */
	public $author_ip;

	/**
	 * Body of the comment.
	 *
	 * @var string
	 */
	public $contents;

	/**
	 * Status. One of `pending`, `approved` and `spam`.
	 *
	 * @var string
	 */
	public $status;

	/**
	 * Notify status. One of `no`, `yes`, `author` and `done`.
	 *
	 * @var string
	 */
	public $notify;

	/**
	 * Date created.
	 *
	 * @var string
	 */
	public $created;

	/**
	 * Returns the node the comment is attached to.
	 *
	 * @return \Icybee\Modules\Nodes\Node
	 */
	protected function get_node()
	{
		global $core;

		return $core->models['nodes'][$this->nid];
	}

	/**
	 * Returns the URL of the comment.
	 *
	 * The URL of the comment is created from the URL of the node and to identifier of the comment
	 * using the following pattern: `{node.url}#comment{commentid}`.
	 *
	 * @return string
	 */
	protected function get_url()
	{
		$node = $this->node;

		return ($node ? $this->node->url : '#unknown-node-' . $this->nid) . '#comment-' . $this->commentid;
	}

	/**
	 * Returns the absolute URL of the comment.
	 *
	 * @return string
	 */
	protected function get_absolute_url()
	{
		$node = $this->node;

		return ($node ? $this->node->absolute_url : '#unknown-node-' . $this->nid) . '#comment-' . $this->commentid;
	}

	/**
	 * Returns the URL of the author's Gravatar.
	 *
	 * @return string
	 */
	protected function get_author_icon()
	{
		$hash = md5(strtolower(trim($this->author_email)));

		return 'http://www.gravatar.com/avatar/' . $hash . '.jpg?' . http_build_query
		(
			array
			(
				'd' => 'identicon'
			)
		);
	}

	/**
	 * Returns an HTML excerpt of the comment.
	 *
	 * @param int $limit The maximum number of words to use to create the excerpt. Defaults to 55.
	 *
	 * @return string
	 */
	public function excerpt($limit=55)
	{
		return \ICanBoogie\excerpt((string) $this, $limit);
	}

	/**
	 * Returns an HTML excerpt of the comment.
	 *
	 * @return string
	 */
	protected function get_excerpt()
	{
		return $this->excerpt();
	}

	/**
	 * Whether the author of the node is the author of the comment.
	 *
	 * @return boolean `true` if the author is the same, `false` otherwise.
	 */
	protected function volatile_get_is_author()
	{
		return $this->node->uid == $this->uid;
	}

	/**
	 * Returns the CSS class names of the comment.
	 *
	 * @return array[string]mixed
	 */
	protected function get_css_class_names()
	{
		return array
		(
			'type' => 'comment',
			'id' => 'comment-' . $this->commentid,
			'author-reply' => $this->is_author
		);
	}

	/**
	 * Return the CSS class of the comment.
	 *
	 * @param string|array $modifiers CSS class names modifiers
	 *
	 * @return string
	 */
	public function css_class($modifiers=null)
	{
		return \Icybee\render_css_class($this->css_class_names, $modifiers);
	}

	/**
	 * Returns the CSS class of the comment.
	 *
	 * @return string
	 */
	protected function get_css_class()
	{
		return $this->css_class();
	}

	/**
	 * Renders the comment into a HTML string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		$str = Textmark_Parser::parse($this->contents);

		return \Icybee\Kses::sanitizeComment($str);
	}
}