<?php

class DbTable_MusicSimilarity extends DZend_Model_DbTable
{
    public function insert($data)
    {
        try {
            return $this->insertCachedWithoutException($data);
        } catch(Zend_Db_Statement_Exception $e) {
            $f = $data['f_artist_music_title_id'];
            $s = $data['s_artist_music_title_id'];

            $db = $this->getAdapter();
            $this->update(
                array('similarity' => $data['similarity']),
                $db->quoteInto('f_artist_music_title_id = ?', $f) .
                $db->quoteInto(' AND s_artist_music_title_id = ?', $s)
            );

            $row = $this->findRowByFArtistMusicTitleIdAndSArtistMusicTitleId(
                $f, $s
            );
            $this->_cache->save($row->id, $this->getCacheKey($data));
            return $row->id;
        }
    }
}
