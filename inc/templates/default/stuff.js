function deliciousPost(URL,title) {
	delicious_username = GetCookie('delicious') ;
	if (!delicious_username) {
		window.open('set_delicious.php','delicious_set_username','toolbar=no,width=300,height=20,top=150,left=150') ;
		// alert('username for del.icio.us is not set!') ;
		return ;
	}
	q=URL;
	p=title;
	void(open('http://del.icio.us/'+delicious_username+'?v=2&noui=yes&jump=close&url='+encodeURIComponent(q)+'&title='+encodeURIComponent(p),'delicious', 'toolbar=no,width=700,height=250'));
}
function furlPost(URL,title) {
	q=URL;
	p=title;

	d=document;
	t=d.selection?(d.selection.type!='None'?d.selection.createRange().text:''):(d.getSelection?d.getSelection():'');
	void(furlit=window.open('http://www.furl.net/storeIt.jsp?t='+escape(title)+'&u='+escape(URL)+'&c='+escape(t),'furlit','scrollbars=no,width=475,height=575,left=75,top=20,status=no,resizable=yes'));furlit.focus();
}
function spurlPost(URL,title) {
  q=URL;
  p=title;
  d=document;
  void(spurlit=window.open('http://www.spurl.net/spurl.php?url='+q+'&amp;title='+p,'spurlit','scrollbars=no,width=475,height=575,left=75,top=20,status=no,resizable=yes'));spurlit.focus();
}
function slashdotPost(URL,title){
  q=URL;
  p=title;
  d=document;
  void(slashdotit=window.open('http://slashdot.org/bookmark.pl?url='+q+'title='+p,'slashdotit','scrollbars=no,width=475,height=575,left=75,top=20,status=no,resizable=yes'));slashdotit.focus();
}
function playPodcast(URL) {
  q=URL;
  void(playPodcastWin=window.open('flash/player.php?url='+q,'playPodcastWin','scrollbars=no,width=400,height=15,left=75,top=20,status=no,resizable=no'));playPodcastWin.focus();
}
function checkFeed(URL) {
	q=URL;
  void(checkFeedWin=window.open('http://feedvalidator.org/check.cgi?url='+q,'checkFeedWin','scrollbars=no,width=475,height=575,left=75,top=20,status=no,resizable=yes'));playPodcastWin.focus();

}