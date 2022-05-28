import './i18n' /* set window.tranaction, before everything */
import './bbcode'
import './tooltipster'
import './dropdownMenu'
import Lightbox from './Lightbox'
import filterSlot from './filterSlot'
import toggleTab from './toggleTab'
import toggleEdition from './toggleEdition'
import toggleGroup from './toggleGroup'
import toggleAny from './toggleAny'
import toggleSearchTorrentAdvanced from './toggleSearchTorrentAdvanced'
import toggleTorrentDetail from './toggleTorrentDetail'
import {
  UploadImage,
  imgUpload,
  imgUploadFillBBCode,
  imgCopy,
  imgAllowDrop,
  imgDrop,
} from './imageUpload'

window.lightbox = new Lightbox()
window.globalapp = {
  filterSlot,
  toggleTab,
  toggleEdition,
  toggleGroup,
  toggleAny,
  toggleSearchTorrentAdvanced,
  toggleTorrentDetail,
  UploadImage,
  imgUpload,
  imgUploadFillBBCode,
  imgCopy,
  imgAllowDrop,
  imgDrop,
}
