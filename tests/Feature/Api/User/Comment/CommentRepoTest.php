<?php

use App\Models\Mogou;
use App\Models\SubMogou;
use App\Repo\User\Comments\UserCommentRepo;
use Database\Seeders\CategorySeeder;
use Database\Seeders\MogousCategorySeeder;
use Database\Seeders\MogouSeeder;
use Database\Seeders\SubMogouSeeder;
use Database\Seeders\SubscriptionSeeder;
use Illuminate\Http\UploadedFile;
use Tests\Support\TestStorage;
use Tests\Support\UserAuthenticated;

uses()->group('user', 'api', 'comment');
uses(UserAuthenticated::class);
uses(TestStorage::class);

beforeEach(function () {
    $this->repo = new UserCommentRepo;
    config(['control.test.mogous_count' => 3]);

    $this->seed([
        SubscriptionSeeder::class,
        CategorySeeder::class,
        MogouSeeder::class,
        MogousCategorySeeder::class,
        SubMogouSeeder::class,
    ]);
    $this->setupUser();
    $this->bootStorage();

    $mogou = Mogou::first();
    $subMogou = SubMogou::where('mogou_id', $mogou->id)->inRandomOrder()->first();

    $this->text_comment = [
        'content' => 'Test Comment 1',
        'image_path' => null,
        'mogou_id' => $mogou->id,
        'sub_mogou_id' => $subMogou->id,
        'parent_comment_id' => null,
        'user_id' => $this->user->id,
    ];
    $this->photoComment = $this->text_comment;
    $this->photoComment['image_path'] = UploadedFile::fake()->image('comment_two.jpg');

});

it('user can store comment on mogou post', function () {
    $expectedComment = 'Test Comment 1';
    $this->repo->storeComment($this->text_comment);

    $this->assertDatabaseHas('comments', [
        'content' => $expectedComment,
        'image_path' => null,
        'mogou_id' => $this->text_comment['mogou_id'],
        'parent_comment_id' => null,
        'user_id' => $this->text_comment['user_id'],
    ]);
});

it('another User Can reply to the comment', function () {
    $comment = $this->repo->storeComment($this->text_comment);

    $this->repo->replyComment($comment, $this->photoComment);

    $this->assertDatabaseHas('comments', [
        'content' => $this->photoComment['content'],
    ]);

    $this->assertDatabaseHas('comments', [
        'content' => $this->text_comment['content'],
        'parent_comment_id' => $comment->id,
    ]);
});

it('user can remove comment', function () {
    $comment = $this->repo->storeComment($this->text_comment);

    $this->repo->remove($comment);

    $this->assertDatabaseMissing('comments', [
        'id' => $comment->id,
    ]);
});

it('user can comment with photo', function () {
    $this->photoComment['sub_mogou_id'] = null;
    $comment = $this->repo->storeComment($this->photoComment);

    $this->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'content' => $comment->content,
    ]);

    $this->assertInStorage("comments/$comment->id/$comment->image_path");
});

it('can get the comments with nested reply', function () {
    $this->text_comment['sub_mogou_id'] = null;
    $comment = $this->repo->storeComment($this->text_comment);

    $this->text_comment['content'] = 'Test Comment 2';

    $this->repo->replyComment($comment, $this->text_comment);

    $comments = $this->repo->getInstance($comment->mogou)->get()->toArray();

    // check that comments is nested
    $this->assertCount(1, $comments);
    $this->assertCount(1, $comments[0]['child_comments']);
    $this->assertEquals($comments[0]['id'], $comments[0]['child_comments'][0]['parent_comment_id']);

});
