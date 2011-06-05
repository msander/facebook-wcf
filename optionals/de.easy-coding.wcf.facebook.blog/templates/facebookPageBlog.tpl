<ul class="dataList">
        {foreach from=$blogEntries key=entryID item=entry}
                {assign var=userID value=$entry->userID}
                {assign var=user value=$entry->getUser()}
                <li class="container-{cycle values='1,2'}">
                        <div class="containerIcon">
                                {if $user->getAvatar()}
                                        {assign var=x value=$user->getAvatar()->setMaxSize(24, 24)}
                                        <a href="index.php?page=User&amp;userID={@$userID}{@SID_ARG_2ND}" title="{lang username=$user->username}wcf.user.viewProfile{/lang}">{@$user->getAvatar()}</a>
                                {else}
                                        <a href="index.php?page=User&amp;userID={@$userID}{@SID_ARG_2ND}" title="{lang username=$user->username}wcf.user.viewProfile{/lang}"><img src="{@RELATIVE_WCF_DIR}images/avatars/avatar-default.png" alt="" style="width: 24px; height: 24px" /></a>
                                {/if}
                        </div>
                        <div class="containerContent">
                                <h4><a href="index.php?page=UserBlogEntry&amp;entryID={@$entryID}{@SID_ARG_2ND}#profileContent" title="{$entry.excerpt}">{$entry.subject}</a></h4>
                                <p class="light firstPost">{lang}wcf.user.blog.box.entryBy{/lang}</p>
                        </div>
                </li>
        {/foreach}
</ul>
