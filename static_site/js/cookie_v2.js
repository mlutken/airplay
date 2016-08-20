﻿var CookieInformerBooklet=CookieInformerBooklet||{};
var CookieInformerBooklet={config:{title:'Websitet anvender cookies til at huske dine indstillinger, statistik og at målrette annoncer. <a href="/cookie-og-privatlivspolitik" target="_blank">Læs mere >> </a>',buttonText:'Cookies'},
init:function(options){jQuery.extend(this.config,options);if(this.getCookie()=='hidden'){return}this.createElements();this.bindEvents()},createElements:function(){this.$container=jQuery('<div />',
{id:'cookieInformerBooklet'}).css({bottom:'0',height:'1px',left:'0',overflow:'visible',position:'fixed',right:'0',zIndex:'11001'});
this.$button=jQuery('<div />',{text:'Ok','class':'cookie-button'}).css({backgroundColor:'#FAFAFA',borderColor:'#93948c',borderRadius:'5px 5px 0 0',borderStyle:'solid solid none',borderWidth:'1px 1px medium',bottom:'0',boxShadow:'0 0 6px #bbbbbb',color:'#444444',cursor:'pointer',display:'block',height:'24px',lineHeight:'24px',opacity:'0.9',padding:'0 14px',zIndex:'2',transition:'all 0.3s linear'}).hover(function(){jQuery(this).css({borderColor:'#44b2f0',boxShadow:'0 0 6px white'})},
function(){jQuery(this).css({borderColor:'#93948c',boxShadow:'0 0 6px #bbbbbb'})});
this.$content=jQuery('<div />',{'class':'content'}).css({backgroundColor:'#EFEFEF',borderColor:'#E55D09',borderRadius:'0 0 0 0',borderStyle:'solid none none',borderWidth:'4px 0px medium',bottom:'0',color:'#444444',display:'block',left:'0px',opacity:'0.8',position:'absolute',right:'0px',zIndex:'1'});
this.$contentTitle=jQuery('<h3 />',{text:this.config.title});this.$contentText=jQuery('<p />').css({paddingLeft:"20px",background:"url('http://static.airplay-music.dk/images/site/site_sprite.png') -333px -90px no-repeat"}).html(this.config.text);this.$content.append(this.$contentTitle).append(this.$contentText);this.$container.appendTo('body').append(this.$button).append(this.$content)},bindEvents:function(){this.$button.click(this.setCookie)},setCookie:
function(){var name='CookieInformerBooklet',value='hidden',days=20*365;if(days){var date=new Date();date.setTime(date.getTime()+(days*24*60*60*1000));var expires="; expires="+date.toGMTString()}else var expires="";document.cookie=name+"="+value+expires+"; path=/";CookieInformerBooklet.$container.fadeOut(function(){jQuery(this).remove()})},getCookie:function(){var c_name='CookieInformerBooklet';
if(document.cookie.length>0){c_start=document.cookie.indexOf(c_name+"=");if(c_start!=-1){c_start=c_start+c_name.length+1;c_end=document.cookie.indexOf(";",c_start);if(c_end==-1){c_end=document.cookie.length}return unescape(document.cookie.substring(c_start,c_end))}}return""}};(function(){var v="1.5.1";if(window.jQuery===undefined||compareVersion(v,window.jQuery.fn.jquery))
initCookieInformerBookmarklet();
function initCookieInformerBookmarklet(){jQuery(document).ready(function(){var options={title:'',text:'Websitet anvender cookies til at huske dine indstillinger, statistik og at målrette annoncer. <a href="/cookie-og-privatlivspolitik" target="_blank">Læs mere >> </a>',buttonText:'Cookies'};
CookieInformerBooklet.init(options)})}
function compareVersion(version1,version2){if('undefined'===typeof version1){throw new Error("compareVersion needs at least one parameter.");}version2=version2||jQuery.fn.jquery;
if(version1==version2){return 0}var v1=normalize(version1);var v2=normalize(version2);var len=Math.max(v1.length,v2.length);
for(var i=0;i<len;i++){v1[i]=v1[i]||0;v2[i]=v2[i]||0;if(v1[i]==v2[i]){continue}return v1[i]>v2[i]?1:0}return 0};function normalize(version){return jQuery.map(version.split('.'),function(value){return parseInt(value,10)})}})();